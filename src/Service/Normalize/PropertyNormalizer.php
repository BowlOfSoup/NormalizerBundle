<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Normalize;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Annotation\Translate;
use BowlOfSoup\NormalizerBundle\Model\ObjectBag;
use BowlOfSoup\NormalizerBundle\Model\Store;
use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use Doctrine\Persistence\Proxy;
use Symfony\Contracts\Translation\TranslatorInterface;

class PropertyNormalizer extends AbstractNormalizer
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor */
    private $propertyExtractor;

    public function __construct(
        ClassExtractor $classExtractor,
        TranslatorInterface $translator,
        AnnotationExtractor $annotationExtractor,
        PropertyExtractor $propertyExtractor
    ) {
        parent::__construct($classExtractor, $translator, $annotationExtractor);

        $this->propertyExtractor = $propertyExtractor;
    }

    /**
     * @param \BowlOfSoup\NormalizerBundle\Model\Context|string|null $context
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    public function normalize(
        Normalizer $sharedNormalizer,
        ObjectBag $objectBag,
        $context
    ): array {
        $object = $objectBag->getObject();
        $objectName = $objectBag->getObjectName();
        $objectIdentifier = $objectBag->getObjectIdentifier();

        $this->sharedNormalizer = $sharedNormalizer;
        $this->handleContext($context);
        $this->nameAndClassStore[$objectIdentifier] = new Store();

        $normalizedProperties = [];

        $this->processedDepthObjects[$objectName] = $this->processedDepth;
        $classAnnotation = $this->getClassAnnotation($object);

        $classProperties = $this->propertyExtractor->getProperties($object);
        /** @var \ReflectionProperty $classProperty */
        foreach ($classProperties as $classProperty) {
            $propertyAnnotations = $this->annotationExtractor->getAnnotationsForProperty(Normalize::class, $classProperty);
            if (empty($propertyAnnotations)) {
                continue;
            }

            if ($this->isAlreadyNormalizedForObject($classProperty->getName(), $objectIdentifier, $classProperty->getDeclaringClass()->getName())) {
                // Current $classProperty contains a value that has already been normalized in a child class.
                continue;
            }

            $classProperty->setAccessible(true);

            if ($object instanceof Proxy && !$object->__isInitialized()) {
                $object->__load();
            }

            $normalizedProperty = $this->normalizeProperty(
                $object,
                $classProperty,
                $propertyAnnotations,
                $classAnnotation
            );
            if (empty($normalizedProperty)) {
                continue;
            }

            $normalizedProperties[] = $normalizedProperty;
            $this->storeNormalizedConstructForObject($classProperty->getName(), $objectIdentifier, $object);
        }

        $this->cleanUpObject($objectIdentifier);

        return $normalizedProperties;
    }

    /**
     * Normalization per (reflected) property.
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    private function normalizeProperty(
        object $object,
        \ReflectionProperty $property,
        array $propertyAnnotations,
        ?Normalize $classAnnotation
    ): array {
        $normalizedProperties = [];

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\Normalize $propertyAnnotation */
        foreach ($propertyAnnotations as $propertyAnnotation) {
            if (!$propertyAnnotation->isGroupValidForConstruct($this->group)) {
                continue;
            }

            $translateAnnotations = $this->annotationExtractor->getAnnotationsForProperty(Translate::class, $property);
            $translationAnnotation = $this->getTranslationAnnotation($translateAnnotations);

            $propertyName = $property->getName();

            // Will throw reflection exception when property is not accessible (because $property->setAccessible() was not used).
            $propertyValue = $property->getValue($object);

            if ($this->skipEmptyValue($propertyValue, $propertyAnnotation, $classAnnotation)) {
                continue;
            }

            $annotationName = $propertyAnnotation->getName();
            if (!empty($annotationName)) {
                $propertyName = $propertyAnnotation->getName();
            }

            // Add to current path, like a breadcrumb where we are when normalizing.
            $this->currentPath[] = $propertyName;
            if (!$this->canCurrentPathBeIncluded($propertyAnnotation->getType())) {
                $this->decreaseCurrentPath();

                continue;
            }

            if ($propertyAnnotation->hasType()) {
                $propertyValue = $this->getValueForPropertyWithType(
                    $object,
                    $property,
                    $propertyValue,
                    $propertyAnnotation,
                    $propertyAnnotation->getType()
                );
            } else {
                // Callback support, only for properties with no type defined.
                $annotationPropertyCallback = $propertyAnnotation->getCallback();
                if (!empty($annotationPropertyCallback)) {
                    $propertyValue = $this->handleCallbackResult(
                        $this->propertyExtractor->getPropertyValueByMethod($object, $annotationPropertyCallback),
                        $propertyAnnotation
                    );
                }
            }

            $propertyValue = (is_array($propertyValue) && empty($propertyValue) ? null : $propertyValue);
            if (null !== $translationAnnotation) {
                $propertyValue = $this->translateValue($propertyValue, $translationAnnotation);
            }

            $normalizedProperties[$propertyName] = $propertyValue;

            $this->decreaseCurrentPath();
        }

        return $normalizedProperties;
    }

    /**
     * Returns values for properties with the annotation property 'type'.
     *
     * @param mixed $propertyValue
     *
     * @throws \ReflectionException
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return mixed|null
     */
    private function getValueForPropertyWithType(
        object $object,
        \ReflectionProperty $property,
        $propertyValue,
        Normalize $propertyAnnotation,
        string $annotationPropertyType
    ) {
        $newPropertyValue = null;
        $annotationPropertyType = strtolower($annotationPropertyType);

        if (static::TYPE_DATETIME === $annotationPropertyType) {
            $newPropertyValue = $this->getValueForPropertyWithDateTime($object, $property, $propertyAnnotation);
        } elseif (static::TYPE_OBJECT === $annotationPropertyType) {
            $newPropertyValue = $this->getValueForPropertyWithTypeObject($object, $propertyValue, $propertyAnnotation);
        } elseif (static::TYPE_COLLECTION === $annotationPropertyType) {
            $newPropertyValue = $this->normalizeReferencedCollection($propertyValue, $propertyAnnotation);
        }

        return $newPropertyValue;
    }

    /**
     * Returns values for properties with annotation type 'datetime'.
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    private function getValueForPropertyWithDateTime(object $object, \ReflectionProperty $property, Normalize $propertyAnnotation): ?string
    {
        $annotationPropertyCallback = $propertyAnnotation->getCallback();
        if (!empty($annotationPropertyCallback)) {
            $propertyValue = $this->handleCallbackResult(
                $this->propertyExtractor->getPropertyValueByMethod($object, $annotationPropertyCallback),
                $propertyAnnotation
            );
        } else {
            $propertyValue = $property->getValue($object);
        }

        if ($propertyValue instanceof \DateTimeInterface) {
            return $propertyValue->format($propertyAnnotation->getFormat());
        }

        return null;
    }

    /**
     * Returns values for properties with annotation type 'object'.
     *
     * @param mixed $propertyValue
     *
     * @throws \ReflectionException
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return mixed|null
     */
    private function getValueForPropertyWithTypeObject(object $object, $propertyValue, Normalize $propertyAnnotation)
    {
        if ($this->hasMaxDepth()) {
            return $this->getValueForMaxDepth($propertyValue);
        }
        ++$this->processedDepth;

        $annotationCallback = $propertyAnnotation->getCallback();
        if (!empty($annotationCallback) && is_callable([$propertyValue, $annotationCallback])) {
            --$this->processedDepth;

            return $this->handleCallbackResult($propertyValue->$annotationCallback(), $propertyAnnotation);
        }

        if (empty($propertyValue)) {
            --$this->processedDepth;

            return null;
        }

        $normalizedProperty = $this->normalizeReferencedObject($propertyValue, $object);
        --$this->processedDepth;

        return $normalizedProperty;
    }
}
