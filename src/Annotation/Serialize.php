<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

/**
 * Register serialization, encoding properties.
 *
 * @Annotation
 *
 * @Target({"CLASS"})
 */
class Serialize extends AbstractAnnotation
{
    /** @var array|array[] */
    private $supportedProperties = [
        'group' => ['type' => 'array'],
        'wrapElement' => ['type' => 'string'],
        'sortProperties' => ['type' => 'boolean'],
    ];

    /** @var string|null */
    private $wrapElement = null;

    /** @var bool */
    private $sortProperties = false;

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

    public function getWrapElement(): ?string
    {
        return $this->wrapElement;
    }

    public function mustSortProperties(): bool
    {
        return $this->sortProperties;
    }
}
