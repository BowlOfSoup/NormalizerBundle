<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target({"PROPERTY","METHOD"})
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Translate extends AbstractAnnotation
{
    private const array SUPPORTED_PROPERTIES = [
        'group' => ['type' => 'array'],
        'domain' => ['type' => 'string'],
        'locale' => ['type' => 'string'],
    ];

    private ?string $domain = null;
    private ?string $locale = null;

    public function __construct(
        array|string|null $domain = null,
        array|string|null $group = null,
        ?string $locale = null,
    ) {
        // Legacy Doctrine annotations: single array argument
        if (is_array($domain) && null === $group && 1 === func_num_args()) {
            $this->applyProperties($domain);

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
            'domain' => is_string($domain) ? $domain : null,
            'group' => $groupValue,
            'locale' => $locale,
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

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
