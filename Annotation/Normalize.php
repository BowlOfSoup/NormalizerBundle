<?php

namespace BowlOfSoup\NormalizerBundle\Annotation;

use InvalidArgumentException;

/**
 * Register normalization properties.
 *
 * @Annotation
 * @Target({"CLASS","PROPERTY"})
 */
class Normalize
{
    /** @var string */
    const EXCEPTION_EMPTY = 'Parameter "%s" of annotation "%s" cannot be empty.';

    /** @var string */
    const EXCEPTION_TYPE = 'Wrong datatype used for property "%s" for annotation "%s"';

    /** @var string */
    const EXCEPTION_TYPE_SUPPORTED = 'Type "%s" of annotation "%s" is not supported.';

    /** @var array */
    private $supportedProperties = array(
        'name' => array('type' => 'string'),
        'group' => array('type' => 'array'),
        'type' => array('type' => 'string', 'assert' => array('collection', 'datetime', 'object')),
        'format' => array('type' => 'string'),
        'callback' => array('type' => 'string'),
        'skipEmpty' => array('type' => 'boolean'),
    );

    /** @var string */
    private $name;

    /** @var array */
    private $group = array();

    /** @var string */
    private $type;

    /** @var string */
    private $format;

    /** @var string */
    private $callback;

    /** @var boolean */
    private $skipEmpty = false;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        foreach ($this->supportedProperties as $supportedPropertyKey => $supportedPropertyOptions) {
            $this->handleProperty($properties, $supportedPropertyKey, $supportedPropertyOptions);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

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
     * @return string
     */
    public function getFormat()
    {
        if (empty($this->format)) {
            return 'Y-m-d';
        }

        return $this->format;
    }

    /**
     * @return string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return bool
     */
    public function getSkipEmpty()
    {
        return $this->skipEmpty;
    }

    /**
     * @param array  $properties
     * @param string $propertyName
     * @param array  $propertyOptions
     */
    private function handleProperty(array $properties, $propertyName, array $propertyOptions = array())
    {
        if (isset($properties[$propertyName])) {
            $this->checkEmpty($properties[$propertyName], $propertyName);

            if (isset($propertyOptions['type'])) {
                $this->checkType($propertyOptions['type'], $properties[$propertyName], $propertyName);
            }

            if (isset($propertyOptions['assert'])) {
                $this->checkAssert($propertyOptions['assert'], $properties[$propertyName], $propertyName);
            }

            $this->$propertyName = $properties[$propertyName];
        }
    }

    /**
     * @param string $property
     * @param string $propertyName
     *
     * @throws \InvalidArgumentException
     */
    private function checkEmpty($property, $propertyName)
    {
        if (empty($property) && false !== $property) {
            throw new InvalidArgumentException(
                sprintf(static::EXCEPTION_EMPTY, $propertyName, __CLASS__)
            );
        }
    }

    /**
     * @param string $type
     * @param string $property
     * @param string $propertyName
     *
     * @throws \InvalidArgumentException
     */
    private function checkType($type, $property, $propertyName)
    {
        if ($type !== gettype($property)) {
            throw new InvalidArgumentException(
                sprintf(static::EXCEPTION_TYPE, $propertyName, __CLASS__)
            );
        }
    }

    /**
     * @param array  $assertions
     * @param string $property
     *
     * @throws \InvalidArgumentException
     */
    private function checkAssert(array $assertions, $property)
    {
        if (!in_array(strtolower($property), $assertions)) {
            throw new InvalidArgumentException(
                sprintf(static::EXCEPTION_TYPE_SUPPORTED, $property, __CLASS__)
            );
        }
    }
}
