<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

class MethodExtractor
{
    /** @var string */
    public const TYPE = 'method';

    /**
     * @param object|string $object
     */
    public function getMethods($object): array
    {
        if (!is_object($object)) {
            return [];
        }

        $reflectedClass = new \ReflectionClass($object);

        $methods = $reflectedClass->getMethods(
            \ReflectionMethod::IS_PUBLIC |
            \ReflectionMethod::IS_PROTECTED |
            \ReflectionMethod::IS_PRIVATE |
            \ReflectionMethod::IS_STATIC
        );

        $parentClass = $reflectedClass->getParentClass();
        if ($parentClass) {
            // Since PHP >= 8 it will not get the private methods of an abstract, get it explicitly.
            $parentPrivateMethods = $parentClass->getMethods(\ReflectionMethod::IS_PRIVATE);
            $methods = array_merge($methods, $parentPrivateMethods);

            // Make sure methods are unique (will happen if PHP < 8)
            $uniqueMethods = [];
            foreach ($methods as $key => $method) {
                $id = $method->class . ':' . $method->name;
                if (isset($uniqueMethods[$id])) {
                    // @codeCoverageIgnoreStart
                    unset($methods[$key]);
                    // @codeCoverageIgnoreEnd
                }
                $uniqueMethods[$id] = true;
            }
        }

        return $methods;
    }
}
