<?php

namespace BowlOfSoup\NormalizerBundle\Annotation;

/**
 * Register serialization, encoding properties.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class Serialize extends AbstractAnnotation
{
    /** @var array */
    private $supportedProperties = array(
        'group' => array('type' => 'array'),
        'type' => array('type' => 'string', 'assert' => array('json', 'xml', 'yaml')),
        'wrapElement' => array('type' => 'string'),
    );

    /** @var string */
    private $wrapElement;

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
    public function getWrapElement()
    {
        return $this->wrapElement;
    }
}
