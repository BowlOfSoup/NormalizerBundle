<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

abstract class AbstractAnnotation
{
    protected const string EXCEPTION_EMPTY = 'Parameter "%s" of annotation "%s" cannot be empty.';
    protected const string EXCEPTION_TYPE = 'Wrong datatype used for property "%s" for annotation "%s"';
    protected const string EXCEPTION_TYPE_SUPPORTED = 'Type "%s" of annotation "%s" is not supported.';
    protected const string EXCEPTION_UNKNOWN_PROPERTY = 'Property "%s" of annotation "%s" is unknown.';

    protected array $group = [];

    abstract protected function getSupportedProperties(): array;

    public function getGroup(): array
    {
        return $this->group;
    }

    /**
     * Check if annotation property 'group' matches up with requested group.
     */
    public function isGroupValidForConstruct(?string $group): bool
    {
        $annotationGroup = $this->getGroup();

        return (empty($group) || in_array($group, $annotationGroup)) && (!empty($group) || empty($annotationGroup));
    }

    protected function validateProperty(string $propertyName, mixed $propertyValue): void
    {
        $supportedProperties = $this->getSupportedProperties();

        if (!array_key_exists($propertyName, $supportedProperties)) {
            throw new \InvalidArgumentException(sprintf(static::EXCEPTION_UNKNOWN_PROPERTY, $propertyName, static::class));
        }

        if (null !== $propertyValue) {
            $this->validateProperties($propertyValue, $propertyName, $supportedProperties[$propertyName], static::class);
        }
    }

    protected function validateProperties(mixed $property, string $propertyName, array $propertyOptions, string $annotation): bool
    {
        if ($this->isEmpty($property)) {
            throw new \InvalidArgumentException(sprintf(static::EXCEPTION_EMPTY, $propertyName, $annotation));
        }

        if (isset($propertyOptions['type'])
            && !$this->hasCorrectType($propertyOptions['type'], $property)
        ) {
            throw new \InvalidArgumentException(sprintf(static::EXCEPTION_TYPE, $propertyName, $annotation));
        }

        if (isset($propertyOptions['assert'])
            && !$this->hasValidAssertion($propertyOptions['assert'], $property)
        ) {
            throw new \InvalidArgumentException(sprintf(static::EXCEPTION_TYPE_SUPPORTED, $property, $annotation));
        }

        return true;
    }

    private function isEmpty(mixed $property): bool
    {
        return 0 !== $property && empty($property) && false !== $property;
    }

    private function hasCorrectType(mixed $type, mixed $property): bool
    {
        return $type === gettype($property);
    }

    private function hasValidAssertion(array $assertions, string $property): bool
    {
        return in_array(strtolower($property), $assertions, false);
    }
}
