<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Normalize;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Annotation\Translate;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class PropertyNormalizer extends AbstractNormalizer
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor */
    private $propertyExtractor;

    public function __construct(
        ClassExtractor $classExtractor,
        TranslatorInterface $translator,
        PropertyExtractor $propertyExtractor
    ) {
        parent::__construct($classExtractor, $translator);

        $this->propertyExtractor = $propertyExtractor;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    public function normalize(
        Normalizer $sharedNormalizer,
        string $objectName,
        object $object,
        ?string $group
    ): array {
        $this->sharedNormalizer = $sharedNormalizer;
        $this->group = $group;

        $this->processedDepthObjects[$objectName] = $this->processedDepth;

        $classProperties = $this->propertyExtractor->getProperties($object);
        $normalizedProperties = [];
        foreach ($classProperties as $classProperty) {
            $propertyAnnotations = $this->getPropertyAnnotations($objectName, $classProperty, Normalize::class);
            if (empty($propertyAnnotations)) {
                continue;
            }

            $classProperty->setAccessible(true);

            $normalizedProperties[] = $this->normalizeProperty(
                $object,
                $classProperty,
                $propertyAnnotations,
                $this->getClassAnnotation($objectName, $object)
            );
        }

        return $normalizedProperties;
    }

    private function getPropertyAnnotations(string $objectName, \ReflectionProperty $classProperty, string $annotationClass): array
    {
        $propertyName = $classProperty->getName();

        if (isset($this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName])) {
            $propertyAnnotations = $this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName];
        } else {
            $propertyAnnotations = $this->propertyExtractor->extractPropertyAnnotations($classProperty, $annotationClass);
            $this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName] = $propertyAnnotations;
        }

        return $propertyAnnotations;
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

            $translateAnnotations = $this->getPropertyAnnotations(get_class($object), $property, Translate::class);
            $translationAnnotation = $this->getTranslationAnnotation($translateAnnotations);

            $propertyName = $property->getName();
            $propertyValue = $this->propertyExtractor->getPropertyValue($object, $property);

            if ($this->skipEmptyValue($propertyValue, $propertyAnnotation, $classAnnotation)) {
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

            $annotationName = $propertyAnnotation->getName();
            if (!empty($annotationName)) {
                $propertyName = $propertyAnnotation->getName();
            }

            $propertyValue = (is_array($propertyValue) && empty($propertyValue) ? null : $propertyValue);
            if (null !== $translationAnnotation) {
                $propertyValue = $this->translateValue($propertyValue, $translationAnnotation);
            }

            $normalizedProperties[$propertyName] = $propertyValue;
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

        if ('datetime' === $annotationPropertyType) {
            $newPropertyValue = $this->getValueForPropertyWithDateTime($object, $property, $propertyAnnotation);
        } elseif ('object' === $annotationPropertyType) {
            $newPropertyValue = $this->getValueForPropertyWithTypeObject($object, $propertyValue, $propertyAnnotation);
        } elseif ('collection' === $annotationPropertyType) {
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
