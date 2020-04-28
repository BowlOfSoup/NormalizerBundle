<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionProperty;

class ClassExtractor
{
    /** @var string */
    public const TYPE = 'class';

    /** @var bool */
    public const GET_PROPERTIES_ONLY_PRIVATES = true;

    /** @var \Doctrine\Common\Annotations\Reader */
    protected $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Extract annotations set on class level.
     *
     * @param object|array $object
     * @param object|string $annotation
     *
     * @throws \ReflectionException
     */
    public function extractClassAnnotations($object, $annotation): array
    {
        if (!is_object($object)) {
            return [];
        }

        $annotations = [];
        $reflectedClass = new ReflectionClass($object);

        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectedClass);
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation instanceof $annotation) {
                $annotations[] = $classAnnotation;
            }
        }

        return $annotations;
    }

    /**
     * Get all properties for a given class.
     *
     * @param object $object
     *
     * @throws \ReflectionException
     */
    public function getProperties($object): array
    {
        if (!is_object($object)) {
            return [];
        }

        $reflectedClass = new ReflectionClass($object);
        $classProperties = $this->getClassProperties($reflectedClass);

        // Also get (private) variables from parent class.
        $privateProperties = [];
        while ($reflectedClass = $reflectedClass->getParentClass()) {
            $privateProperties[] = $this->getClassProperties($reflectedClass, static::GET_PROPERTIES_ONLY_PRIVATES);
        }

        $classProperties = array_merge($classProperties, ...$privateProperties);

        return $classProperties;
    }

    /**
     * Get class properties through reflection.
     *
     * @return \ReflectionProperty[]
     */
    private function getClassProperties(ReflectionClass $reflectedClass, bool $onlyPrivates = false): array
    {
        if ($onlyPrivates) {
            return $reflectedClass->getProperties(ReflectionProperty::IS_PRIVATE);
        }

        return $reflectedClass->getProperties(
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_PROTECTED |
            ReflectionProperty::IS_PRIVATE
        );
    }
}
