<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Annotation\AbstractAnnotation;
use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Exception;
use ReflectionProperty;

class Normalizer
{
    /** @var bool */
    const DISABLE_CIRCULAR_REFERENCE_CHECK = true;

    /** @var ClassExtractor */
    protected $classExtractor;

    /** @var PropertyExtractor */
    protected $propertyExtractor;

    /** @var array */
    private $processedObjects = array();

    /** @var int */
    private $processedDepth = 0;

    /** @var int */
    private $maxDepth;

    /** @var string|null */
    private $group = null;

    /**
     * @param ClassExtractor    $classExtractor
     * @param PropertyExtractor $propertyExtractor
     */
    public function __construct(
        ClassExtractor $classExtractor,
        PropertyExtractor $propertyExtractor
    ) {
        $this->classExtractor = $classExtractor;
        $this->propertyExtractor = $propertyExtractor;
    }

    /**
     * Normalize an object, for a specific group.
     *
     * @param object      $object
     * @param string|null $group
     *
     * @return array
     */
    public function normalize($object, $group = null)
    {
        $this->group = $group;
        $this->processedObjects = array();

        if (!is_object($object)) {
            return null;
        }

        return $this->normalizeObject($object);
    }

    /**
     * Get properties for given object, annotations per property and begin normalizing.
     *
     * In this method, 'new Normalize(array())' is used for PHP < 5.5 support.
     * Normally we should use 'Normalize::class'
     *
     * @param object $object
     * @param bool   $disableCircularReferenceCheck
     *
     * @return array
     */
    private function normalizeObject($object, $disableCircularReferenceCheck = false)
    {
        if (!$disableCircularReferenceCheck) {
            $this->processedObjects[] = get_class($object);
        }
        $normalizedProperties = array();

        $classProperties = $this->classExtractor->getProperties($object);
        foreach ($classProperties as $classProperty) {
            $propertyAnnotations = $this->propertyExtractor->extractPropertyAnnotations(
                $classProperty,
                new Normalize(array())
            );
            if (empty($propertyAnnotations)) {
                continue;
            }

            $classProperty->setAccessible(true);

            $normalizedProperties = array_merge(
                $normalizedProperties,
                $this->normalizeProperty(
                    $object,
                    $classProperty,
                    $propertyAnnotations,
                    $this->getClassAnnotation($object)
                )
            );
        }

        return $normalizedProperties;
    }

    /**
     * Get class annotation for specified group.
     *
     * First group entry will be used, duplicate definitions will be gracefully ignored.
     *
     * In this method, 'new Normalize(array())' is used for PHP < 5.5 support,
     * Normally we should use 'Normalize::class'
     *
     * @param object $object
     *
     * @return Normalize|null
     */
    private function getClassAnnotation($object)
    {
        $classAnnotations = $this->classExtractor->extractClassAnnotations($object, new Normalize(array()));
        if (empty($classAnnotations)) {
            return null;
        }

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\Normalize $classAnnotation */
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation->isGroupValidForProperty($this->group)) {
                return $classAnnotation;
            }
        }

        return null;
    }

    /**
     * Normalization per (reflected) property.
     *
     * @param object             $object
     * @param ReflectionProperty $property
     * @param array              $propertyAnnotations
     * @param Normalize|null     $classAnnotation
     *
     * @return array
     */
    private function normalizeProperty(
        $object,
        ReflectionProperty $property,
        array $propertyAnnotations,
        Normalize $classAnnotation = null
    ) {
        $normalizedProperties = array();
        $skipEmpty = (null !== $classAnnotation ? $classAnnotation->getSkipEmpty() : false);
        if (null !== $classAnnotation && null === $this->maxDepth) {
            $this->maxDepth = $classAnnotation->getMaxDepth();
        }

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\Normalize $propertyAnnotation */
        foreach ($propertyAnnotations as $propertyAnnotation) {
            if (!$propertyAnnotation->isGroupValidForProperty($this->group)) {
                continue;
            }

            $propertyName = $property->getName();
            $propertyValue = $this->propertyExtractor->getPropertyValue($object, $property);

            if ((true === $skipEmpty || true === $propertyAnnotation->getSkipEmpty()) && empty($propertyValue)) {
                continue;
            }

            $annotationPropertyType = $propertyAnnotation->getType();
            if (null !== ($annotationPropertyType)) {
                $propertyValue = $this->getValueForPropertyWithType(
                    $object,
                    $property,
                    $propertyValue,
                    $propertyAnnotation,
                    $annotationPropertyType
                );
            } else {
                // Callback support, only for properties with no type defined.
                $annotationPropertyCallback = $propertyAnnotation->getCallback();
                if (!empty($annotationPropertyCallback)) {
                    $propertyValue = $this->propertyExtractor->getPropertyValueByMethod(
                        $object,
                        $annotationPropertyCallback
                    );
                }
            }

            $annotationName = $propertyAnnotation->getName();
            if (!empty($annotationName)) {
                $propertyName = $propertyAnnotation->getName();
            }

            $propertyValue = (is_array($propertyValue) && empty($propertyValue) ? null : $propertyValue);
            $normalizedProperties[$propertyName] = $propertyValue;
        }

        return $normalizedProperties;
    }

    /**
     * Check if annotation property 'group' matches up with requested group.
     *
     * @param AbstractAnnotation $propertyAnnotation
     *
     * @return bool
     */
    private function isGroupValidForProperty(AbstractAnnotation $annotation)
    {
        $annotationPropertyGroup = $annotation->getGroup();

        if ((!empty($this->group) && !in_array($this->group, $annotationPropertyGroup)) ||
            (empty($this->group) && !empty($annotationPropertyGroup))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Returns values for properties with the annotation property 'type'.
     *
     * @param object             $object
     * @param ReflectionProperty $property
     * @param mixed              $propertyValue
     * @param Normalize          $propertyAnnotation
     * @param string             $annotationPropertyType
     *
     * @throws Exception
     *
     * @return mixed|null
     */
    private function getValueForPropertyWithType(
        $object,
        ReflectionProperty $property,
        $propertyValue,
        Normalize $propertyAnnotation,
        $annotationPropertyType
    ) {
        $newPropertyValue = null;
        $annotationPropertyType = strtolower($annotationPropertyType);

        if ('datetime' === $annotationPropertyType) {
            // Always try to use get method for DateTime properties, get method can contain default settings.
            $propertyValue = $this->propertyExtractor->getPropertyValue(
                $object,
                $property,
                PropertyExtractor::FORCE_PROPERTY_GET_METHOD
            );

            if ($propertyValue instanceof DateTime) {
                $newPropertyValue = $propertyValue->format($propertyAnnotation->getFormat());
            }
        } elseif ('object' === $annotationPropertyType) {
            $newPropertyValue = $this->getValueForPropertyWithTypeObject($object, $propertyValue, $propertyAnnotation);
        } elseif ('collection' === $annotationPropertyType) {
            $newPropertyValue = $this->normalizeReferencedCollection($propertyValue, $propertyAnnotation);
        }

        return $newPropertyValue;
    }

    /**
     * Returns values for properties with annotation type 'object'.
     *
     * @param object    $object
     * @param mixed     $propertyValue
     * @param Normalize $propertyAnnotation
     *
     * @throws Exception
     *
     * @return mixed|null
     */
    private function getValueForPropertyWithTypeObject($object, $propertyValue, Normalize $propertyAnnotation)
    {
        if ($this->hasMaxDepth()) {
            return $this->getValueForMaxDepth($propertyValue);
        }
        ++$this->processedDepth;

        $annotationCallback = $propertyAnnotation->getCallback();
        if (!empty($annotationCallback) && is_callable(array($propertyValue, $annotationCallback))) {
            return $propertyValue->$annotationCallback();
        }

        if (null === $propertyValue) {
            return null;
        }

        $normalizedProperty = $this->normalizeReferencedObject($propertyValue, $object);
        --$this->processedDepth;

        return $normalizedProperty;
    }

    /**
     * Normalize a referenced object, handles circular references.
     *
     * @param object $object
     * @param object $parentObject
     *
     * @throws Exception
     *
     * @return array
     */
    private function normalizeReferencedObject($object, $parentObject)
    {
        $normalizedProperty = null;

        $className = get_class($object);
        if (is_object($object) && !in_array($className, $this->processedObjects)) {
            $normalizedProperty = $this->normalizeObject($object);

            if (empty($normalizedProperty)) {
                return null;
            };
        }

        if (empty($normalizedProperty)) {
            $normalizedProperty = $this->propertyExtractor->getId($object);
            if (null === $normalizedProperty) {
                throw new Exception(
                    'Circular reference on: ' .$className . ' called from: ' . get_class($parentObject) .
                    '. If possible, prevent this by adding a getId() method to ' . $className
                );
            }

            return array('id' => $normalizedProperty);
        }

        return $normalizedProperty;
    }

    /**
     * Normalize a property with 'collection' type.
     *
     * A Collection can be anything that is iteratable, such as a Doctrine ArrayCollection, or just an array.
     *
     * @param mixed     $propertyValue
     * @param Normalize $propertyAnnotation
     *
     * @return array
     */
    private function normalizeReferencedCollection($propertyValue, Normalize $propertyAnnotation)
    {
        $normalizedCollection = array();

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

            if (!empty($annotationCallback) && is_callable(array($collectionItem, $annotationCallback))) {
                $normalizedCollection[] = $collectionItem->$annotationCallback();
            } else {
                $normalizedObject = $this->normalizeObject(
                    $collectionItem,
                    static::DISABLE_CIRCULAR_REFERENCE_CHECK
                );

                $normalizedCollection[] = (!empty($normalizedObject) ? $normalizedObject : null);
            }
            --$this->processedDepth;
        }

        return $normalizedCollection;
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    private function hasMaxDepth()
    {
        return null !== $this->maxDepth && ($this->processedDepth + 1) > $this->maxDepth;
    }

    /**
     * @param object $object
     *
     * @return int|string
     *
     * @throws Exception
     */
    private function getValueForMaxDepth($object)
    {
        $propertyValue = $this->propertyExtractor->getId($object);
        if (null === $propertyValue) {
            throw new Exception(
                'Maximal depth reached, but no identifier found. '.
                'Prevent this by adding a getId() method to ' . get_class($object)
            );
        }

        return $propertyValue;
    }
}
