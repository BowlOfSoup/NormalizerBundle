<?php

namespace BowlOfSoup\NormalizerBundle\Annotation;

/**
 * Register normalization properties.
 *
 * @Annotation
 * @Target({"CLASS","PROPERTY"})
 */
class Normalize extends AbstractAnnotation
{
    /** @var array */
    private $supportedProperties = array(
        'name' => array('type' => 'string'),
        'group' => array('type' => 'array'),
        'type' => array('type' => 'string', 'assert' => array('collection', 'datetime', 'object')),
        'format' => array('type' => 'string'),
        'callback' => array('type' => 'string'),
        'normalizeCallbackResult' => array('type' => 'boolean'),
        'skipEmpty' => array('type' => 'boolean'),
        'maxDepth' => array('type' => 'integer'),
    );

    /** @var string */
    private $name;

    /** @var string */
    private $format;

    /** @var string */
    private $callback;

    /** @var boolean */
    private $normalizeCallbackResult = false;

    /** @var boolean */
    private $skipEmpty = false;

    /** @var int */
    private $maxDepth;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        foreach ($this->supportedProperties as $supportedPropertyKey => $supportedPropertyOptions) {
            if ($this->validateProperties($properties, $supportedPropertyKey, $supportedPropertyOptions, __CLASS__)) {
                $this->$supportedPropertyKey = $properties[$supportedPropertyKey];
            }
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
     * @return bool
     */
    public function hasType()
    {
        return null !== $this->type;
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
     * @return boolean
     */
    public function mustNormalizeCallbackResult()
    {
        return $this->normalizeCallbackResult;
    }

    /**
     * @return bool
     */
    public function getSkipEmpty()
    {
        return $this->skipEmpty;
    }

    /**
     * @return int
     */
    public function getMaxDepth()
    {
        return $this->maxDepth;
    }
}
