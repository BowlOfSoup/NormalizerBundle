<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Annotation;

abstract class AbstractAnnotation
{
    protected const EXCEPTION_EMPTY = 'Parameter "%s" of annotation "%s" cannot be empty.';
    protected const EXCEPTION_TYPE = 'Wrong datatype used for property "%s" for annotation "%s"';
    protected const EXCEPTION_TYPE_SUPPORTED = 'Type "%s" of annotation "%s" is not supported.';
    protected const EXCEPTION_UNKNOWN_PROPERTY = 'Property "%s" of annotation "%s" is unknown.';

    protected array $group = [];

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

        return (empty($group) || in_array($group, $annotationGroup, false)) && (!empty($group) || empty($annotationGroup));
    }

    /**
     * @param mixed $property
     */
    protected function validateProperties($property, string $propertyName, array $propertyOptions, string $annotation): bool
    {
        if ($this->isEmpty($property)) {
            throw new \InvalidArgumentException(sprintf(static::EXCEPTION_EMPTY, $propertyName, $annotation));
        }

        if (isset($propertyOptions['type']) &&
            !$this->hasCorrectType($propertyOptions['type'], $property)
        ) {
            throw new \InvalidArgumentException(sprintf(static::EXCEPTION_TYPE, $propertyName, $annotation));
        }

        if (isset($propertyOptions['assert']) &&
            !$this->hasValidAssertion($propertyOptions['assert'], $property)
        ) {
            throw new \InvalidArgumentException(sprintf(static::EXCEPTION_TYPE_SUPPORTED, $property, $annotation));
        }

        return true;
    }

    /**
     * @param mixed $property
     */
    private function isEmpty($property): bool
    {
        return 0 !== $property && empty($property) && false !== $property;
    }

    /**
     * @param mixed $type
     * @param mixed $property
     */
    private function hasCorrectType($type, $property): bool
    {
        return $type === gettype($property);
    }

    private function hasValidAssertion(array $assertions, string $property): bool
    {
        return in_array(strtolower($property), $assertions, false);
    }
}
