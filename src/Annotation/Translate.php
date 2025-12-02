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
    private array $supportedProperties = [
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
        // Support old array-based initialization (for Doctrine annotations)
        if (is_array($domain) && null === $group && 1 === func_num_args()) {
            $properties = $domain;
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
            'domain' => is_string($domain) ? $domain : null,
            'group' => $groupValue,
            'locale' => $locale,
        ];

        foreach ($properties as $propertyName => $propertyValue) {
            if (null === $propertyValue) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            if (!array_key_exists($propertyName, $this->supportedProperties)) {
                // @codeCoverageIgnoreStart
                throw new \InvalidArgumentException(sprintf(static::EXCEPTION_UNKNOWN_PROPERTY, $propertyName, self::class));
                // @codeCoverageIgnoreEnd
            }

            if ($this->validateProperties($propertyValue, $propertyName, $this->supportedProperties[$propertyName], self::class)) {
                $this->$propertyName = $propertyValue;
            }
        }
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
