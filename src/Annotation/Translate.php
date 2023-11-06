<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY","METHOD"})
 */
class Translate extends AbstractAnnotation
{
    private array $supportedProperties = [
        'group' => ['type' => 'array'],
        'domain' => ['type' => 'string'],
        'locale' => ['type' => 'string'],
    ];

    private ?string $domain = null;
    private ?string $locale = null;

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

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
