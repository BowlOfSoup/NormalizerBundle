<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

use Attribute;

/**
 * Register normalization properties.
 *
 * @Annotation
 * @Target({"CLASS","PROPERTY","METHOD"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Normalize extends AbstractAnnotation
{
    protected ?string $type = null;

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

    public function __construct(
        array|string|null $name = null,
        array|string|null $group = null,
        ?string $type = null,
        ?string $format = null,
        ?string $callback = null,
        ?bool $normalizeCallbackResult = null,
        ?bool $skipEmpty = null,
        ?int $maxDepth = null,
    ) {
        // Support old array-based initialization (for Doctrine annotations)
        if (is_array($name) && null === $group && 1 === func_num_args()) {
            $properties = $name;
            foreach ($properties as $propertyName => $propertyValue) {
                if (!array_key_exists($propertyName, $this->supportedProperties)) {
                    throw new \InvalidArgumentException(sprintf(static::EXCEPTION_UNKNOWN_PROPERTY, $propertyName, self::class));
                }

                if ($this->validateProperties($propertyValue, $propertyName, $this->supportedProperties[$propertyName], self::class)) {
                    $this->$propertyName = $propertyValue;
                }
            }

            return;
        }

        // Support PHP 8 attribute named parameters
        $groupValue = $this->group;
        if (is_array($group)) {
            $groupValue = $group;
        } elseif (is_string($group)) {
            $groupValue = [$group];
        }

        $properties = [
            'name' => is_string($name) ? $name : null,
            'group' => $groupValue,
            'type' => $type,
            'format' => $format,
            'callback' => $callback,
            'normalizeCallbackResult' => $normalizeCallbackResult ?? false,
            'skipEmpty' => $skipEmpty ?? false,
            'maxDepth' => $maxDepth,
        ];

        foreach ($properties as $propertyName => $propertyValue) {
            if (null === $propertyValue && 'name' !== $propertyName && 'format' !== $propertyName && 'callback' !== $propertyName && 'maxDepth' !== $propertyName && 'type' !== $propertyName) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            if (!array_key_exists($propertyName, $this->supportedProperties)) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException(sprintf(static::EXCEPTION_UNKNOWN_PROPERTY, $propertyName, self::class));
                // @codeCoverageIgnoreEnd
            }

            if (null !== $propertyValue && $this->validateProperties($propertyValue, $propertyName, $this->supportedProperties[$propertyName], self::class)) {
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
