<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

use Attribute;

/**
 * Register serialization, encoding properties.
 *
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Serialize extends AbstractAnnotation
{
    private array $supportedProperties = [
        'group' => ['type' => 'array'],
        'wrapElement' => ['type' => 'string'],
        'sortProperties' => ['type' => 'boolean'],
    ];

    private ?string $wrapElement = null;
    private bool $sortProperties = false;

    public function __construct(
        array|string|null $wrapElement = null,
        array|string|null $group = null,
        ?bool $sortProperties = null,
    ) {
        // Support old array-based initialization (for Doctrine annotations)
        if (is_array($wrapElement) && null === $group && 1 === func_num_args()) {
            $properties = $wrapElement;
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
            'wrapElement' => is_string($wrapElement) ? $wrapElement : null,
            'group' => $groupValue,
            'sortProperties' => $sortProperties ?? false,
        ];

        foreach ($properties as $propertyName => $propertyValue) {
            if (null === $propertyValue && 'wrapElement' !== $propertyName) {
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

    public function getWrapElement(): ?string
    {
        return $this->wrapElement;
    }

    public function mustSortProperties(): bool
    {
        return $this->sortProperties;
    }
}
