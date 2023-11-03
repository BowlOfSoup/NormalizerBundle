<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

class MethodExtractor
{
    /** @var string */
    public const TYPE = 'method';

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

        return $reflectedClass->getMethods(
            \ReflectionMethod::IS_PUBLIC |
            \ReflectionMethod::IS_PROTECTED |
            \ReflectionMethod::IS_PRIVATE |
            \ReflectionMethod::IS_STATIC
        );
    }
}
