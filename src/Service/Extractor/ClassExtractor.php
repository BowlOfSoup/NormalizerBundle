<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

class ClassExtractor extends AbstractExtractor
{
    /** @var string */
    public const TYPE = 'class';

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
        $reflectedClass = new \ReflectionClass($object);

        $classAnnotations = $this->annotationReader->getClassAnnotations($reflectedClass);
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation instanceof $annotation) {
                $annotations[] = $classAnnotation;
            }
        }

        return $annotations;
    }
}
