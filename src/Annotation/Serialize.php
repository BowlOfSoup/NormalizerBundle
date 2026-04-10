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
    private const array SUPPORTED_PROPERTIES = [
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
        // Legacy Doctrine annotations: single array argument
        if (is_array($wrapElement) && null === $group && 1 === func_num_args()) {
            $this->applyProperties($wrapElement);

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
            'wrapElement' => is_string($wrapElement) ? $wrapElement : null,
            'group' => $groupValue,
            'sortProperties' => $sortProperties ?? false,
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

    public function getWrapElement(): ?string
    {
        return $this->wrapElement;
    }

    public function mustSortProperties(): bool
    {
        return $this->sortProperties;
    }
}
