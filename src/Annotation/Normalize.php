<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

/**
 * Register normalization properties.
 *
 * @Annotation
 * @Target({"CLASS","PROPERTY","METHOD"})
 */
class Normalize extends AbstractAnnotation
{
    private array $supportedProperties = [
        'name' => ['type' => 'string'],
        'group' => ['type' => 'array'],
        'type' => ['type' => 'string', 'assert' => ['collection', 'datetime', 'object']],
        'format' => ['type' => 'string'],
        'callback' => ['type' => 'string'],
        'normalizeCallbackResult' => ['type' => 'boolean'],
        'skipEmpty' => ['type' => 'boolean'],
        'maxDepth' => ['type' => 'integer'],
    ];

    private ?string $name = null;
    private ?string $format = null;
    private ?string $callback = null;
    private bool $normalizeCallbackResult = false;
    private bool $skipEmpty = false;
    private ?int $maxDepth = null;
    protected ?string $type = null;

    public function __construct(array $properties)
    {
        foreach ($properties as $propertyName => $propertyValue) {
            if (!array_key_exists($propertyName, $this->supportedProperties)) {
                throw new \InvalidArgumentException(sprintf(static::EXCEPTION_UNKNOWN_PROPERTY, $propertyName, self::class));
            }

            if ($this->validateProperties($propertyValue, $propertyName, $this->supportedProperties[$propertyName], self::class)) {
                $this->$propertyName = $propertyValue;
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

    public function getType(): ?string
    {
        return $this->type;
    }
}
