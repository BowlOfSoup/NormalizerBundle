<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

class MethodExtractor extends AbstractExtractor
{
    /** @var string */
    public const TYPE = 'method';

    /**
     * Extract all annotations for a (reflected) class method.
     *
     * @param string|object $annotation
     */
    public function extractMethodAnnotations(\ReflectionMethod $objectMethod, $annotation): array
    {
        $annotations = [];

        $methodAnnotations = $this->annotationReader->getMethodAnnotations($objectMethod);
        foreach ($methodAnnotations as $methodAnnotation) {
            if ($methodAnnotation instanceof $annotation) {
                $annotations[] = $methodAnnotation;
            }
        }

        return $annotations;
    }

    /**
     * @param object|string $object
     *
     * @throws \ReflectionException
     */
    public function getMethods($object): array
    {
        if (!is_object($object)) {
            return [];
        }

        $reflectedClass = new \ReflectionClass($object);
        $classMethods = $this->getClassMethods($reflectedClass);

        // Also get (private) methods from parent class.
        $privateMethods = [];
        while ($reflectedClass = $reflectedClass->getParentClass()) {
            $privateMethods[] = $this->getClassMethods($reflectedClass, static::GET_ONLY_PRIVATES);
        }

        return array_merge($classMethods, ...$privateMethods);
    }

    private function getClassMethods(\ReflectionClass $reflectedClass, bool $onlyPrivates = false): array
    {
        if ($onlyPrivates) {
            return $reflectedClass->getMethods(\ReflectionMethod::IS_PRIVATE);
        }

        return $reflectedClass->getMethods(
            \ReflectionMethod::IS_PUBLIC |
            \ReflectionMethod::IS_PROTECTED |
            \ReflectionMethod::IS_PRIVATE
        );
    }
}
