<?php

namespace BowlOfSoup\NormalizerBundle\Annotation;

use InvalidArgumentException;

abstract class AbstractAnnotation
{
    /** @var string */
    const EXCEPTION_EMPTY = 'Parameter "%s" of annotation "%s" cannot be empty.';

    /** @var string */
    const EXCEPTION_TYPE = 'Wrong datatype used for property "%s" for annotation "%s"';

    /** @var string */
    const EXCEPTION_TYPE_SUPPORTED = 'Type "%s" of annotation "%s" is not supported.';

    /** @var array */
    protected $group = array();

    /** @var string */
    protected $type;

    /**
     * @return array
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Check if annotation property 'group' matches up with requested group.
     *
     * @param string $group
     *
     * @return boolean
     */
    public function isGroupValidForProperty($group)
    {
        $annotationGroup = $this->getGroup();

        return ((empty($group) || in_array($group, $annotationGroup)) && (!empty($group) || empty($annotationGroup)));
    }

    /**
     * @param array  $properties
     * @param string $propertyName
     * @param array  $propertyOptions
     * @param string $annotation
     *
     * @return boolean
     */
    protected function validateProperties(array $properties, $propertyName, array $propertyOptions, $annotation)
    {
        if (isset($properties[$propertyName])) {
            if ($this->isEmpty($properties[$propertyName])) {
                throw new InvalidArgumentException(sprintf(static::EXCEPTION_EMPTY, $propertyName, $annotation));
            }

            if (isset($propertyOptions['type']) &&
                !$this->hasCorrectType($propertyOptions['type'], $properties[$propertyName])
            ) {
                throw new InvalidArgumentException(sprintf(static::EXCEPTION_TYPE, $propertyName, $annotation));
            }

            if (isset($propertyOptions['assert']) &&
                !$this->hasValidAssertion($propertyOptions['assert'], $properties[$propertyName])
            ) {
                throw new InvalidArgumentException(
                    sprintf(static::EXCEPTION_TYPE_SUPPORTED, $properties[$propertyName], $annotation)
                );
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $property
     *
     * @return boolean
     */
    private function isEmpty($property)
    {
        return 0 !== $property && empty($property) && false !== $property;
    }

    /**
     * @param string $type
     * @param string $property
     *
     * @return boolean
     */
    private function hasCorrectType($type, $property)
    {
        return $type === gettype($property);
    }

    /**
     * @param array  $assertions
     * @param string $property
     *
     * @return boolean
     */
    private function hasValidAssertion(array $assertions, $property)
    {
        return in_array(strtolower($property), $assertions);
    }
}
