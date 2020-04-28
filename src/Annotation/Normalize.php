<?php

namespace BowlOfSoup\NormalizerBundle\Annotation;

/**
 * Register normalization properties.
 *
 * @Annotation
 * @Target({"CLASS","PROPERTY","METHOD"})
 */
class Normalize extends AbstractAnnotation
{
    /** @var array */
    private $supportedProperties = [
        'name' => ['type' => 'string'],
        'group' => ['type' => 'array'],
        'type' => ['type' => 'string', 'assert' => ['collection', 'datetime', 'object']],
        'format' => ['type' => 'string'],
        'callback' => ['type' => 'string'],
        'normalizeCallbackResult' => ['type' => 'boolean'],
        'skipEmpty' => ['type' => 'boolean'],
        'maxDepth' => ['type' => 'integer'],
    ];

    /** @var string */
    private $name;

    /** @var string */
    private $format;

    /** @var string */
    private $callback;

    /** @var bool */
    private $normalizeCallbackResult = false;

    /** @var bool */
    private $skipEmpty = false;

    /** @var int */
    private $maxDepth;

    public function __construct(array $properties)
    {
        foreach ($this->supportedProperties as $supportedPropertyKey => $supportedPropertyOptions) {
            if ($this->validateProperties($properties, $supportedPropertyKey, $supportedPropertyOptions, __CLASS__)) {
                $this->$supportedPropertyKey = $properties[$supportedPropertyKey];
            }
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function hasType(): ?bool
    {
        return null !== $this->type;
    }

    public function getFormat(): ?string
    {
        if (empty($this->format)) {
            return 'Y-m-d';
        }

        return $this->format;
    }

    public function getCallback(): ?string
    {
        return $this->callback;
    }

    public function mustNormalizeCallbackResult(): ?bool
    {
        return $this->normalizeCallbackResult;
    }

    public function getSkipEmpty(): ?bool
    {
        return $this->skipEmpty;
    }

    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }
}
