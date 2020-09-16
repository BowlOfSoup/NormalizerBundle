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
    private $supportedProperties = [
        'group' => ['type' => 'array'],
        'wrapElement' => ['type' => 'string'],
        'sortProperties' => ['type' => 'boolean']
    ];

    /** @var string */
    private $wrapElement;

    /** @var bool */
    private $sortProperties = false;

    public function __construct(array $properties)
    {
        foreach ($properties as $propertyName => $propertyValue) {
            if (!array_key_exists($propertyName, $this->supportedProperties)) {
                throw new \InvalidArgumentException(sprintf(static::EXCEPTION_UNKNOWN_PROPERTY, $propertyName, __CLASS__));
            }

            if ($this->validateProperties($propertyValue, $propertyName, $this->supportedProperties[$propertyName], __CLASS__)) {
                $this->$propertyName = $propertyValue;
            }
        }
    }

    public function getWrapElement(): ?string
    {
        return $this->wrapElement;
    }

    public function mustSortProperties(): bool
    {
        return $this->sortProperties;
    }
}
