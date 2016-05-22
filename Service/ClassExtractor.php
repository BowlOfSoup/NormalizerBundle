<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;
use ReflectionProperty;

class ClassExtractor
{
    /** @var bool */
    const GET_PROPERTIES_ONLY_PRIVATES = true;

    /** @var AnnotationReader */
    protected $annotationReader;

    /**
     * @param \Doctrine\Common\Annotations\AnnotationReader $annotationReader
     */
    public function __construct(AnnotationReader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Extract annotations set on class level.
     *
     * @param object $object
     * @param string $annotation
     *
     * @return object|null
     */
    public function extractClassAnnotation($object, $annotation)
    {
        $reflectedClass = new ReflectionClass($object);

        return $this->annotationReader->getClassAnnotation($reflectedClass, $annotation);
    }

    /**
     * Get all properties for a given class.
     *
     * @param object $object
     *
     * @return \ReflectionProperty[]
     */
    public function getProperties($object)
    {
        $reflectedClass = new ReflectionClass($object);
        $classProperties = $this->getClassProperties($reflectedClass);

        // Also get (private) variables from parent class.
        while ($reflectedClass = $reflectedClass->getParentClass()) {
            $classProperties = array_merge(
                $classProperties,
                $this->getClassProperties($reflectedClass, static::GET_PROPERTIES_ONLY_PRIVATES)
            );
        }

        return $classProperties;
    }

    /**
     * Get class properties through reflection.
     *
     * @param \ReflectionClass $reflectedClass
     * @param bool             $onlyPrivates
     *
     * @return \ReflectionProperty[]
     */
    private function getClassProperties(ReflectionClass $reflectedClass, $onlyPrivates = false)
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
