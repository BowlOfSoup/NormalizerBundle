<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use Doctrine\Common\Collections\Collection;

class Normalizer
{
    /** @var ClassExtractor */
    protected $classExtractor;

    /** @var PropertyExtractor */
    protected $propertyExtractor;

    /** @var array */
    private $processedObjects = array();

    /** @var array */
    private $processedObjectCache = array();

    /** @var array */
    private $annotationCache = array();

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
     * Normalize an object or an array of objects, for a specific group.
     *
     * @param mixed       $data
     * @param string|null $group
     *
     * @return array
     */
    public function normalize($data, $group = null)
    {
        $this->group = $group;
        $normalizedData = array();

        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $item) {
                $normalizedData[] = $this->normalize($item, $group);
            }
        } else {
            $normalizedData = $this->normalizeObject($data);
        }
        $this->cleanUp();

        return $normalizedData;
    }

    /**
     * Resets the caches.
     */
    private function cleanUp()
    {
        $this->processedObjects = array();
        $this->processedObjectCache = array();
        $this->annotationCache = array();
        $this->maxDepth = null;
    }

    /**
     * Get properties for given object, annotations per property and begin normalizing.
     *
     * In this method, 'new Normalize(array())' is used for PHP < 5.5 support.
     * Normally we should use 'Normalize::class'
     *
     * @param object $object
     *
     * @return array
     */
    private function normalizeObject($object)
    {
        $normalizedProperties = array();
        $objectName = get_class($object);
        $objectIdentifier = $this->propertyExtractor->getId($object);

        $this->processedObjects[$objectName] = $objectIdentifier;

        // If cached return previously cached normalized object.
        if (null !== $objectIdentifier &&
            array_key_exists($objectName, $this->processedObjectCache) &&
            array_key_exists($objectIdentifier, $this->processedObjectCache[$objectName])
        ) {
            return $this->processedObjectCache[$objectName][$objectIdentifier];
        }
        $this->processedObjectCache[$objectName][$objectIdentifier] = array();

        $classProperties = $this->classExtractor->getProperties($object);
        foreach ($classProperties as $classProperty) {
            $propertyAnnotations = $this->getPropertyAnnotations($objectName, $classProperty);
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
                    $this->getClassAnnotation($objectName, $object)
                )
            );
        }

        // Cache object
        if (null !== $objectIdentifier) {
            $this->processedObjectCache[$objectName][$objectIdentifier] = $normalizedProperties;
        }

        array_pop($this->processedObjects);

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
     * @param string $objectName
     * @param object $object
     *
     * @return Normalize|null
     */
    private function getClassAnnotation($objectName, $object)
    {
        if (isset($this->annotationCache[ClassExtractor::TYPE][$objectName])) {
            $classAnnotations = $this->annotationCache[ClassExtractor::TYPE][$objectName];
        } else {
            $classAnnotations = $this->classExtractor->extractClassAnnotations($object, new Normalize(array()));
            $this->annotationCache[ClassExtractor::TYPE][$objectName] = $classAnnotations;
        }
        if (empty($classAnnotations)) {
            return null;
        }

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\AbstractAnnotation $classAnnotation */
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation->isGroupValidForProperty($this->group)) {
                $this->maxDepth = $classAnnotation->getMaxDepth();

                return $classAnnotation;
            }
        }

        return null;
    }

    /**
     * Get property annotations.
     *
     * @param string             $objectName
     * @param \ReflectionProperty $classProperty
     *
     * @return array
     */
    private function getPropertyAnnotations($objectName, \ReflectionProperty $classProperty)
    {
        $propertyName = $classProperty->getName();

        if (isset($this->annotationCache[PropertyExtractor::TYPE][$objectName][$propertyName])) {
            $propertyAnnotations = $this->annotationCache[PropertyExtractor::TYPE][$objectName][$propertyName];
        } else {
            $propertyAnnotations = $this->propertyExtractor->extractPropertyAnnotations(
                $classProperty,
                new Normalize(array())
            );
            $this->annotationCache[PropertyExtractor::TYPE][$objectName][$propertyName] = $propertyAnnotations;
        }

        return $propertyAnnotations;
    }

    /**
     * Normalization per (reflected) property.
     *
     * @param object             $object
     * @param \ReflectionProperty $property
     * @param array              $propertyAnnotations
     * @param Normalize|null     $classAnnotation
     *
     * @return array
     */
    private function normalizeProperty(
        $object,
        \ReflectionProperty $property,
        array $propertyAnnotations,
        Normalize $classAnnotation = null
    ) {
        $normalizedProperties = array();

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\Normalize $propertyAnnotation */
        foreach ($propertyAnnotations as $propertyAnnotation) {
            if (!$propertyAnnotation->isGroupValidForProperty($this->group)) {
                continue;
            }

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
            $normalizedProperties[$propertyName] = $propertyValue;
        }

        return $normalizedProperties;
    }

    /**
     * Returns values for properties with the annotation property 'type'.
     *
     * @param object              $object
     * @param \ReflectionProperty $property
     * @param mixed               $propertyValue
     * @param Normalize           $propertyAnnotation
     * @param string              $annotationPropertyType
     *
     * @return mixed|null
     */
    private function getValueForPropertyWithType(
        $object,
        \ReflectionProperty $property,
        $propertyValue,
        Normalize $propertyAnnotation,
        $annotationPropertyType
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
     * @param object                                            $object
     * @param \ReflectionProperty                               $property
     * @param \BowlOfSoup\NormalizerBundle\Annotation\Normalize $propertyAnnotation
     *
     * @return string|null
     */
    private function getValueForPropertyWithDateTime($object, $property, Normalize $propertyAnnotation)
    {
        $annotationPropertyCallback = $propertyAnnotation->getCallback();
        if (!empty($annotationPropertyCallback)) {
            $propertyValue = $this->handleCallbackResult(
                $this->propertyExtractor->getPropertyValueByMethod($object, $annotationPropertyCallback),
                $propertyAnnotation
            );
        } else {
            // Always try to use get method for DateTime properties, get method can contain default settings.
            $propertyValue = $this->propertyExtractor->getPropertyValue(
                $object,
                $property,
                PropertyExtractor::FORCE_PROPERTY_GET_METHOD
            );
        }

        if ($propertyValue instanceof \DateTime) {
            return $propertyValue->format($propertyAnnotation->getFormat());
        }

        return null;
    }

    /**
     * Returns values for properties with annotation type 'object'.
     *
     * @param object    $object
     * @param mixed     $propertyValue
     * @param Normalize $propertyAnnotation
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
            return $this->handleCallbackResult($propertyValue->$annotationCallback(), $propertyAnnotation);
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
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return array
     */
    private function normalizeReferencedObject($object, $parentObject)
    {
        $normalizedProperty = null;

        $objectName = get_class($object);
        if (is_object($object) && !$this->isCircularReference($object, $objectName)) {
            $normalizedProperty = $this->normalizeObject($object);

            if (empty($normalizedProperty)) {
                return null;
            };
        }

        if (empty($normalizedProperty)) {
            $normalizedProperty = $this->propertyExtractor->getId($object);
            if (null === $normalizedProperty) {
                throw new BosNormalizerException(
                    'Circular reference on: ' .$objectName . ' called from: ' . get_class($parentObject) .
                    '. If possible, prevent this by adding a getId() method to ' . $objectName
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
                $normalizedCollection[] = $this->handleCallbackResult(
                    $collectionItem->$annotationCallback(),
                    $propertyAnnotation
                );
            } else {
                $normalizedObject = $this->normalizeObject($collectionItem);
                $normalizedCollection[] = (!empty($normalizedObject) ? $normalizedObject : null);
            }
            --$this->processedDepth;
        }

        return $normalizedCollection;
    }

    /**
     * @param object $object
     * @param string $objectName
     *
     * @return bool
     */
    private function isCircularReference($object, $objectName)
    {
        $objectIdentifier = $this->propertyExtractor->getId($object);

        return array_key_exists($objectName, $this->processedObjects) &&
        array_key_exists($objectIdentifier, $this->processedObjectCache[$objectName]);
    }

    /**
     * @return bool
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
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     */
    private function getValueForMaxDepth($object)
    {
        $propertyValue = $this->propertyExtractor->getId($object);
        if (null === $propertyValue) {
            throw new BosNormalizerException(
                'Maximal depth reached, but no identifier found. '.
                'Prevent this by adding a getId() method to ' . get_class($object)
            );
        }

        return $propertyValue;
    }

    /**
     * @param mixed     $propertyValue
     * @param Normalize $propertyAnnotation
     *
     * @return array|
     */
    private function handleCallbackResult($propertyValue, Normalize $propertyAnnotation)
    {
        if (!$propertyAnnotation->mustNormalizeCallbackResult()) {
            return $propertyValue;
        }

        if ($propertyValue instanceof Collection || is_array($propertyValue)) {
            $allObjects = true;
            $normalizedCollection = array();
            foreach ($propertyValue as $item) {
                if (!is_object($item)) {
                    // Values that are not objects will be skipped/cannot be normalized.
                    $allObjects = false;
                    continue;
                }
                $normalizedCollection[] = $this->normalizeObject($item);
            }
            if (empty($normalizedCollection) && !$allObjects) {
                return $propertyValue;
            }

            return $normalizedCollection;
        } else if (is_object($propertyValue)) {
            return $this->normalizeObject($propertyValue);
        }

        return $propertyValue;
    }

    /**
     * @param mixed $value
     * @param \BowlOfSoup\NormalizerBundle\Annotation\Normalize $propertyAnnotation
     * @param \BowlOfSoup\NormalizerBundle\Annotation\Normalize|null $classAnnotation
     *
     * @return bool
     */
    private function skipEmptyValue($value, Normalize $propertyAnnotation, Normalize $classAnnotation = null)
    {
        $skipEmpty = (null !== $classAnnotation ? $classAnnotation->getSkipEmpty() : false);

        if ((true === $skipEmpty || true === $propertyAnnotation->getSkipEmpty()) && empty($value)) {
            return true;
        }

        return false;
    }
}
