<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

/**
 * Register normalization properties.
 *
 * @Annotation
 *
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
        'passdownGroup' => ['type' => 'string'],
    ];

    /** @var string|null */
    private $name = null;

    /** @var string|null */
    private $format = null;

    /** @var string|null */
    private $callback = null;

    /** @var bool */
    private $normalizeCallbackResult = false;

    /** @var bool */
    private $skipEmpty = false;

    /** @var int|null */
    private $maxDepth = null;

    /** @var string|null */
    protected $type = null;

    /** @var string|null */
    private $passdownGroup = null;

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

    public function hasPassdownGroup(): ?bool
    {
        return $this->passdownGroup !== null;
    }

    public function getPassdownGroup(): ?string
    {
        return $this->passdownGroup;
    }
}
