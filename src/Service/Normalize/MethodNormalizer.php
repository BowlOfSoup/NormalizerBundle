<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Normalize;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Annotation\Translate;
use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class MethodNormalizer extends AbstractNormalizer
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor */
    private $methodExtractor;

    public function __construct(
        ClassExtractor $classExtractor,
        TranslatorInterface $translator,
        MethodExtractor $methodExtractor
    ) {
        parent::__construct($classExtractor, $translator);

        $this->methodExtractor = $methodExtractor;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    public function normalize(
        Normalizer $sharedNormalizer,
        string $objectName,
        object $object,
        ?string $group
    ): array {
        $this->sharedNormalizer = $sharedNormalizer;
        $this->group = $group;

        $this->processedDepthObjects[$objectName] = $this->processedDepth;

        $normalizedMethods = [];
        $classAnnotation = $this->getClassAnnotation($objectName, $object);

        $classMethods = $this->methodExtractor->getMethods($object);
        foreach ($classMethods as $classMethod) {
            $methodAnnotations = $this->getMethodAnnotations($objectName, $classMethod, Normalize::class);
            if (empty($methodAnnotations)) {
                continue;
            }

            $classMethod->setAccessible(true);

            $normalizedMethods[] = $this->normalizeMethod(
                $object,
                $classMethod,
                $methodAnnotations,
                $classAnnotation
            );
        }

        return $normalizedMethods;
    }

    /**
     * @throws \ReflectionException
     */
    private function getMethodAnnotations(string $objectName, \ReflectionMethod $classMethod, string $annotationClass): array
    {
        $methodName = $classMethod->getName();

        if (isset($this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName])) {
            $methodAnnotations = $this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName];
        } else {
            $methodAnnotations = $this->methodExtractor->extractMethodAnnotations($classMethod, $annotationClass);
            $this->annotationCache[$annotationClass][MethodExtractor::TYPE][$objectName][$methodName] = $methodAnnotations;
        }

        return $methodAnnotations;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    private function normalizeMethod(
        object $object,
        \ReflectionMethod $method,
        array $methodAnnotations,
        ?Normalize $classAnnotation
    ): array {
        $normalizedProperties = [];

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\Normalize $methodAnnotation */
        foreach ($methodAnnotations as $methodAnnotation) {
            if (!$methodAnnotation->isGroupValidForConstruct($this->group)) {
                continue;
            }

            $translateAnnotations = $this->getMethodAnnotations(get_class($object), $method, Translate::class);
            $translationAnnotation = $this->getTranslationAnnotation($translateAnnotations);

            $methodName = $method->getName();
            $methodValue = $method->invoke($object);

            if ($this->skipEmptyValue($methodValue, $methodAnnotation, $classAnnotation)) {
                continue;
            }

            if ($methodAnnotation->hasType()) {
                $methodValue = $this->getValueForMethodWithType(
                    $object,
                    $method,
                    $methodValue,
                    $methodAnnotation,
                    $methodAnnotation->getType()
                );
            } else {
                // Callback support, only for properties with no type defined.
                $annotationPropertyCallback = $methodAnnotation->getCallback();
                if (!empty($annotationPropertyCallback)) {
                    $this->callbackException($methodName);
                }
            }

            $annotationName = $methodAnnotation->getName();
            if (!empty($annotationName)) {
                $methodName = $methodAnnotation->getName();
            }

            $methodValue = (is_array($methodValue) && empty($methodValue) ? null : $methodValue);
            if (null !== $translationAnnotation) {
                $methodValue = $this->translateValue($methodValue, $translationAnnotation);
            }

            $normalizedProperties[$methodName] = $methodValue;
        }

        return $normalizedProperties;
    }

    /**
     * Returns values for properties with the annotation property 'type'.
     *
     * @param mixed $propertyValue
     *
     * @throws \ReflectionException
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return mixed|null
     */
    private function getValueForMethodWithType(
        object $object,
        \ReflectionMethod $method,
        $methodValue,
        Normalize $methodAnnotation,
        string $annotationMethodType
    ) {
        $newMethodValue = null;
        $annotationMethodType = strtolower($annotationMethodType);

        if ('datetime' === $annotationMethodType) {
            $newMethodValue = $this->getValueForMethodWithDateTime($object, $method, $methodAnnotation);
        } elseif ('object' === $annotationMethodType) {
            $newMethodValue = $this->getValueForMethodWithTypeObject($object, $method, $methodValue, $methodAnnotation);
        } elseif ('collection' === $annotationMethodType) {
            $newMethodValue = $this->normalizeReferencedCollection($methodValue, $methodAnnotation);
        }

        return $newMethodValue;
    }

    /**
     * Returns values for properties with annotation type 'datetime'.
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     */
    private function getValueForMethodWithDateTime(object $object, \ReflectionMethod $method, Normalize $methodAnnotation): ?string
    {
        $methodValue = null;

        $annotationPropertyCallback = $methodAnnotation->getCallback();
        if (!empty($annotationPropertyCallback)) {
            $this->callbackException($method->getName());
        } else {
            $methodValue = $method->invoke($object);
        }

        if ($methodValue instanceof \DateTimeInterface) {
            return $methodValue->format($methodAnnotation->getFormat());
        }

        return null;
    }

    /**
     * Returns values for properties with annotation type 'object'.
     *
     * @param mixed $methodValue
     *
     * @throws \ReflectionException
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return mixed|null
     */
    private function getValueForMethodWithTypeObject(object $object, \ReflectionMethod $method, $methodValue, Normalize $propertyAnnotation)
    {
        if ($this->hasMaxDepth()) {
            return $this->getValueForMaxDepth($methodValue);
        }
        ++$this->processedDepth;

        $annotationCallback = $propertyAnnotation->getCallback();
        if (!empty($annotationCallback)) {
            $this->callbackException($method->getName());
        }

        if (empty($methodValue)) {
            --$this->processedDepth;

            return null;
        }

        $normalizedProperty = $this->normalizeReferencedObject($methodValue, $object);
        --$this->processedDepth;

        return $normalizedProperty;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     */
    private function callbackException(string $methodName): void
    {
        throw new BosNormalizerException(sprintf('A callback is set on method %s. Callbacks are not allowed on methods.', $methodName));
    }
}
