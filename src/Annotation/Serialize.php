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
        'type' => ['type' => 'string', 'assert' => ['xml', 'json']],
        'wrapElement' => ['type' => 'string'],
        'sortProperties' => ['type' => 'boolean']
    ];

    /** @var string */
    protected $type;

    /** @var string */
    private $wrapElement;

    /** @var bool */
    private $sortProperties = false;

    public function __construct(array $properties)
    {
        foreach ($this->supportedProperties as $supportedPropertyKey => $supportedPropertyOptions) {
            if ($this->validateProperties($properties, $supportedPropertyKey, $supportedPropertyOptions, __CLASS__)) {
                $this->$supportedPropertyKey = $properties[$supportedPropertyKey];
            }
        }
    }

    public function getType(): string
    {
        return $this->type;
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
