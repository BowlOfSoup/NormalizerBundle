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
    private const array SUPPORTED_PROPERTIES = [
        'name' => ['type' => 'string'],
        'group' => ['type' => 'array'],
        'type' => ['type' => 'string', 'assert' => ['collection', 'datetime', 'object']],
        'format' => ['type' => 'string'],
        'callback' => ['type' => 'string'],
        'normalizeCallbackResult' => ['type' => 'boolean'],
        'skipEmpty' => ['type' => 'boolean'],
        'maxDepth' => ['type' => 'integer'],
    ];

    protected ?string $type = null;

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
        // Legacy Doctrine annotations: single array argument
        if (is_array($name) && null === $group && 1 === func_num_args()) {
            $this->applyProperties($name);

            return;
        }

        // Support PHP 8 attribute named parameters
        $groupValue = $this->group;
        if (is_array($group)) {
            $groupValue = $group;
        } elseif (is_string($group)) {
            $groupValue = [$group];
        }

        $this->applyProperties([
            'name' => is_string($name) ? $name : null,
            'group' => $groupValue,
            'type' => $type,
            'format' => $format,
            'callback' => $callback,
            'normalizeCallbackResult' => $normalizeCallbackResult ?? false,
            'skipEmpty' => $skipEmpty ?? false,
            'maxDepth' => $maxDepth,
        ]);
    }

    private function applyProperties(array $properties): void
    {
        foreach ($properties as $propertyName => $propertyValue) {
            $this->validateProperty($propertyName, $propertyValue);

            if (null !== $propertyValue) {
                $this->$propertyName = $propertyValue;
            }
        }
    }

    protected function getSupportedProperties(): array
    {
        return self::SUPPORTED_PROPERTIES;
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
