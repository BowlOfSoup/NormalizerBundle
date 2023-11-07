<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Normalize;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Annotation\Translate;
use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Model\ObjectCache;
use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use BowlOfSoup\NormalizerBundle\Service\ObjectHelper;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractNormalizer
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalizer|null */
    protected $sharedNormalizer = null;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor */
    protected $classExtractor;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    protected $translator;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor */
    protected $annotationExtractor;

    /** @var string|null */
    protected $group = null;

    /** @var int|null */
    protected $maxDepth = null;

    /** @var array */
    protected $processedDepthObjects = [];

    /** @var int */
    protected $processedDepth = 0;

    /** @var \BowlOfSoup\NormalizerBundle\Model\Store[]|array|null */
    protected $nameAndClassStore = null;

    public function __construct(
        ClassExtractor $classExtractor,
        TranslatorInterface $translator,
        AnnotationExtractor $annotationExtractor
    ) {
        $this->classExtractor = $classExtractor;
        $this->translator = $translator;
        $this->annotationExtractor = $annotationExtractor;
    }

    public function cleanUp(): void
    {
        $this->maxDepth = null;
    }

    public function cleanUpObject(string $name): void
    {
        // Reset name and class store for given object.
        $this->nameAndClassStore[$name] = null;
    }

    public function cleanUpSession(): void
    {
        $this->annotationExtractor->cleanUp();
    }

    protected function hasMaxDepth(): bool
    {
        return null !== $this->maxDepth && ($this->processedDepth + 1) > $this->maxDepth;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return int|string
     */
    protected function getValueForMaxDepth(object $object)
    {
        $value = $this->classExtractor->getId($object);
        if (null === $value) {
            throw new BosNormalizerException('Maximal depth reached, but no identifier found. Prevent this by adding a getId() method to ' . get_class($object));
        }

        return $value;
    }

    /**
     * Get class annotation for specified group.
     *
     * First group entry will be used, duplicate definitions will be gracefully ignored.
     *
     * @throws \ReflectionException
     */
    protected function getClassAnnotation(object $object): ?Normalize
    {
        $classAnnotations = $this->annotationExtractor->getAnnotationsForClass(Normalize::class, $object);
        if (empty($classAnnotations)) {
            return null;
        }

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\Normalize $classAnnotation */
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation->isGroupValidForConstruct($this->group)) {
                $this->maxDepth = $classAnnotation->getMaxDepth();

                return $classAnnotation;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    protected function skipEmptyValue($value, Normalize $annotation, Normalize $classAnnotation = null): bool
    {
        $skipEmpty = (null !== $classAnnotation ? $classAnnotation->getSkipEmpty() : false);

        return empty($value) && (true === $skipEmpty || true === $annotation->getSkipEmpty());
    }

    /**
     * Normalize a referenced object, handles circular references.
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    protected function normalizeReferencedObject(object $object, object $parentObject): ?array
    {
        $normalizedConstruct = null;
        $objectName = get_class($object);

        if (is_object($object) && !$this->isCircularReference($object, $objectName)) {
            $normalizedConstruct = $this->sharedNormalizer->normalizeObject($object, $this->group);

            if (empty($normalizedConstruct)) {
                return null;
            }
        }

        if (empty($normalizedConstruct)) {
            $normalizedConstruct = $this->classExtractor->getId($object);
            if (null === $normalizedConstruct) {
                throw new BosNormalizerException('Circular reference on: ' . $objectName . ' called from: ' . get_class($parentObject) . '. If possible, prevent this by adding a getId() method to ' . $objectName);
            }

            return ['id' => $normalizedConstruct];
        }

        return $normalizedConstruct;
    }

    /**
     * Normalize a property with 'collection' type.
     *
     * A Collection can be anything that is iteratable, such as a Doctrine ArrayCollection, or just an array.
     *
     * @param mixed $propertyValue
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    protected function normalizeReferencedCollection($propertyValue, Normalize $propertyAnnotation): ?array
    {
        $normalizedCollection = [];

        if (!$propertyValue instanceof Collection && !is_array($propertyValue)) {
            return null;
        }

        $annotationCallback = $propertyAnnotation->getCallback();
        foreach ($propertyValue as $collectionItem) {
            if (!is_object($collectionItem)) {
                // If not an object, annotation property type="collection" was useless.
                continue;
            }

            if ($this->hasMaxDepth()) {
                $normalizedCollection[] = $this->getValueForMaxDepth($collectionItem);
                continue;
            }
            ++$this->processedDepth;

            if (!empty($annotationCallback) && is_callable([$collectionItem, $annotationCallback])) {
                $normalizedCollection[] = $this->handleCallbackResult(
                    $collectionItem->$annotationCallback(),
                    $propertyAnnotation
                );
            } else {
                $normalizedObject = $this->sharedNormalizer->normalizeObject($collectionItem, $this->group);
                $normalizedCollection[] = (!empty($normalizedObject) ? $normalizedObject : null);
            }
            --$this->processedDepth;
        }

        return $normalizedCollection;
    }

    /**
     * @param mixed $propertyValue
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     *
     * @return array|string|null
     */
    protected function handleCallbackResult($propertyValue, Normalize $propertyAnnotation)
    {
        if (!$propertyAnnotation->mustNormalizeCallbackResult()) {
            return $propertyValue;
        }

        if ($propertyValue instanceof Collection || is_array($propertyValue)) {
            $allObjects = true;
            $normalizedCollection = [];
            foreach ($propertyValue as $item) {
                if (!is_object($item)) {
                    // Values that are not objects will be skipped/cannot be normalized.
                    $allObjects = false;
                    continue;
                }
                $normalizedCollection[] = $this->sharedNormalizer->normalizeObject($item, $this->group);
            }
            if (empty($normalizedCollection) && !$allObjects) {
                return $propertyValue;
            }

            return $normalizedCollection;
        } elseif (is_object($propertyValue)) {
            return $this->sharedNormalizer->normalizeObject($propertyValue, $this->group);
        }

        return $propertyValue;
    }

    /**
     * @param \BowlOfSoup\NormalizerBundle\Annotation\Translate[] $translateAnnotations
     */
    protected function getTranslationAnnotation(array $translateAnnotations, bool $emptyGroup = false): ?Translate
    {
        if (empty($translateAnnotations)) {
            return null;
        }

        $group = ($emptyGroup) ? null : $this->group;

        $translationAnnotation = null;
        foreach ($translateAnnotations as $translateAnnotation) {
            if (!$translateAnnotation->isGroupValidForConstruct($group)) {
                continue;
            }

            // By overwriting the return variable, the last valid annotation on the property/method is taken.
            $translationAnnotation = $translateAnnotation;
        }
        // Annotation found, but no explicit group. Try again with no group.
        if (null === $translationAnnotation) {
            // Don't try again if get with no group given to prevent
            return (!$emptyGroup) ? $this->getTranslationAnnotation($translateAnnotations, true) : null;
        }

        return $translationAnnotation;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function translateValue($value, Translate $translationAnnotation)
    {
        if (!is_string($value)) {
            return $value;
        }

        return $this->translator->trans($value, [], $translationAnnotation->getDomain(), $translationAnnotation->getLocale());
    }

    /**
     * Checks a construct (property/method) against an existing entry in the store.
     * If found, the construct has already been normalized.
     */
    protected function isAlreadyNormalizedForObject(string $constructName, string $baseObjectName, string $actualObjectName): bool
    {
        if (array_key_exists($baseObjectName, $this->nameAndClassStore) && $this->nameAndClassStore[$baseObjectName]->has($constructName)) {
            $object = $this->nameAndClassStore[$baseObjectName]->get($constructName);

            return is_subclass_of($object, $actualObjectName);
        }

        return false;
    }

    protected function storeNormalizedConstructForObject(string $constructName, string $baseObjectName, object $object): void
    {
        $this->nameAndClassStore[$baseObjectName]->set($constructName, $object);
    }

    private function isCircularReference(object $object, string $objectName): bool
    {
        $objectIdentifier = ObjectHelper::getObjectIdentifier($object);

        if (isset($this->processedDepthObjects[$objectName]) && $this->processedDepth <= $this->processedDepthObjects[$objectName]) {
            return false;
        }

        return ObjectCache::hasObjectByNameAndIdentifier($objectName, $objectIdentifier);
    }
}
