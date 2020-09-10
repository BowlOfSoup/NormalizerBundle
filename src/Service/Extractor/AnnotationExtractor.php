<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Persistence\Proxy;

class AnnotationExtractor
{
    /** @var \Doctrine\Common\Annotations\Reader */
    protected $annotationReader;

    /** @var array */
    private $annotationCache = [];

    /**
     * @codeCoverageIgnore
     */
    public function __construct(string $cacheDir = null, bool $debugMode = false)
    {
        if (null !== $cacheDir) {
            $cacheDir = $cacheDir . '/annotations';
            $this->createDirectory($cacheDir);

            if ($this->directoryExits($cacheDir)) {
                $this->annotationReader = new CachedReader(new AnnotationReader(), new FilesystemCache($cacheDir), $debugMode);
            }
        }

        $this->annotationReader = new CachedReader(new AnnotationReader(), new ArrayCache(), $debugMode);
    }

    public function setAnnotationReader(Reader $annotationReader): void
    {
        $this->annotationReader = $annotationReader;
    }

    public function getAnnotationsForProperty(string $annotationClass, \ReflectionProperty $property, string $objectName): array
    {
        $propertyName = $property->getName();

        if (isset($this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName])) {
            $validPropertyAnnotations = $this->annotationCache[$annotationClass][PropertyExtractor::TYPE][$objectName][$propertyName];
        } else {
            $validPropertyAnnotations = [];

            $allPropertyAnnotations = $this->annotationReader->getPropertyAnnotations($property);
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
    public function getAnnotationsForMethod(string $annotationClass, \ReflectionMethod $objectMethod, string $objectName): array
    {
        $methodName = $objectMethod->getName();

        if (isset($this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName])) {
            $validMethodAnnotations = $this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName];
        } else {
            $validMethodAnnotations = [];

            if ($objectMethod->getDeclaringClass()->implementsInterface(Proxy::class)
                && false !== $objectMethod->getDeclaringClass()->getParentClass()
                && empty($this->annotationReader->getMethodAnnotations($objectMethod))
                && $objectMethod->getDeclaringClass()->getParentClass()->hasMethod($objectMethod->getName())
            ) {
                $objectMethod = $objectMethod->getDeclaringClass()->getParentClass()->getMethod($objectMethod->getName());
            }

            $allMethodAnnotations = $this->annotationReader->getMethodAnnotations($objectMethod);
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
     * @param object|array $object
     *
     * @throws \ReflectionException
     */
    public function getAnnotationsForClass(string $annotation, $object): array
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
            $allClassAnnotations = $this->annotationReader->getClassAnnotations($reflectedClass);
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
    private function directoryExits($dir): bool
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
