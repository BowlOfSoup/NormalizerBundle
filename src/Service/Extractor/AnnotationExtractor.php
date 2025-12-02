<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\PsrCachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Persistence\Proxy;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class AnnotationExtractor
{
    public const string CACHE_NS = 'bos_annotations';

    protected Reader $annotationReader;

    private array $annotationCache = [];

    /**
     * @codeCoverageIgnore
     */
    public function __construct(?string $cacheDir = null, bool $debugMode = false)
    {
        if (null !== $cacheDir) {
            $this->createDirectory($cacheDir);

            if ($this->directoryExits($cacheDir)) {
                $this->annotationReader = new PsrCachedReader(new AnnotationReader(), new FilesystemAdapter(self::CACHE_NS, 0, $cacheDir), $debugMode);

                return;
            }
        }

        $this->annotationReader = new PsrCachedReader(new AnnotationReader(), new ArrayAdapter(), $debugMode);
    }

    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function cleanUp(): void
    {
        $this->annotationCache = [];
    }

    public function getAnnotationsForProperty(string $annotationClass, \ReflectionProperty $property): array
    {
        $propertyName = $property->getName();
        $objectName = $property->getDeclaringClass()->getName();

        if (isset($this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName])) {
            $validPropertyAnnotations = $this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName];
        } else {
            $validPropertyAnnotations = [];

            // Read PHP 8 attributes first
            $attributes = $property->getAttributes($annotationClass);
            foreach ($attributes as $attribute) {
                try {
                    $validPropertyAnnotations[] = $attribute->newInstance();
                } catch (\Error $e) {
                    throw new \InvalidArgumentException(sprintf('%s (%s): %s', $objectName, $propertyName, $e->getMessage()), 0, $e);
                }
            }

            // Read docblock annotations (Doctrine)
            try {
                $allPropertyAnnotations = $this->annotationReader->getPropertyAnnotations($property);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf('%s (%s): %s', $objectName, $propertyName, $e->getMessage()), 0, $e);
            }
            foreach ($allPropertyAnnotations as $propertyAnnotation) {
                if ($propertyAnnotation instanceof $annotationClass) {
                    $validPropertyAnnotations[] = $propertyAnnotation;
                }
            }

            $this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName] = $validPropertyAnnotations;
        }

        return $validPropertyAnnotations;
    }

    /**
     * Extract all annotations for a (reflected) class method.
     *
     * @throws \ReflectionException
     */
    public function getAnnotationsForMethod(string $annotationClass, \ReflectionMethod $objectMethod): array
    {
        /** @var string $methodName */
        $methodName = $objectMethod->getName();
        $objectName = $objectMethod->getDeclaringClass()->getName();

        if (isset($this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName])) {
            $validMethodAnnotations = $this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName];
        } else {
            $validMethodAnnotations = [];

            if ($objectMethod->getDeclaringClass()->implementsInterface(Proxy::class)
                && false !== $objectMethod->getDeclaringClass()->getParentClass()
                && empty($this->annotationReader->getMethodAnnotations($objectMethod))
                && empty($objectMethod->getAttributes($annotationClass))
                && $objectMethod->getDeclaringClass()->getParentClass()->hasMethod($objectMethod->getName())
            ) {
                $objectMethod = $objectMethod->getDeclaringClass()->getParentClass()->getMethod($objectMethod->getName());
            }

            // Read PHP 8 attributes first
            $attributes = $objectMethod->getAttributes($annotationClass);
            foreach ($attributes as $attribute) {
                try {
                    $validMethodAnnotations[] = $attribute->newInstance();
                } catch (\Error $e) {
                    throw new \InvalidArgumentException(sprintf('%s (%s): %s', $objectName, $methodName, $e->getMessage()), 0, $e);
                }
            }

            // Read docblock annotations (Doctrine)
            try {
                $allMethodAnnotations = $this->annotationReader->getMethodAnnotations($objectMethod);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf('%s (%s): %s', $objectName, $methodName, $e->getMessage()), 0, $e);
            }
            foreach ($allMethodAnnotations as $methodAnnotation) {
                if ($methodAnnotation instanceof $annotationClass) {
                    $validMethodAnnotations[] = $methodAnnotation;
                }
            }

            $this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName] = $validMethodAnnotations;
        }

        return $validMethodAnnotations;
    }

    /**
     * Extract annotations set on class level.
     *
     * @throws \ReflectionException
     */
    public function getAnnotationsForClass(string $annotation, object|array $object): array
    {
        if (!is_object($object)) {
            return [];
        }
        $reflectedClass = new \ReflectionClass($object);
        $className = $reflectedClass->getName();

        if (isset($this->annotationCache[ClassExtractor::TYPE][$className])) {
            $validClassAnnotations = $this->annotationCache[ClassExtractor::TYPE][$className];
        } else {
            $validClassAnnotations = [];

            // Read PHP 8 attributes first
            $attributes = $reflectedClass->getAttributes($annotation);
            foreach ($attributes as $attribute) {
                try {
                    $validClassAnnotations[] = $attribute->newInstance();
                } catch (\Error $e) {
                    throw new \InvalidArgumentException(sprintf('%s: %s', $className, $e->getMessage()), 0, $e);
                }
            }

            // Read docblock annotations (Doctrine)
            try {
                $allClassAnnotations = $this->annotationReader->getClassAnnotations($reflectedClass);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(sprintf('%s: %s', $className, $e->getMessage()), 0, $e);
            }

            foreach ($allClassAnnotations as $classAnnotation) {
                if ($classAnnotation instanceof $annotation) {
                    $validClassAnnotations[] = $classAnnotation;
                }
            }

            $this->annotationCache[ClassExtractor::TYPE][$className] = $validClassAnnotations;
        }

        return $validClassAnnotations;
    }

    /**
     * @codeCoverageIgnore
     */
    private function directoryExits(string $dir): bool
    {
        return is_dir($dir);
    }

    /**
     * @codeCoverageIgnore
     */
    private function createDirectory(string $dir): void
    {
        if ($this->directoryExits($dir)) {
            return;
        }

        @mkdir($dir, 0777, true);
    }
}
