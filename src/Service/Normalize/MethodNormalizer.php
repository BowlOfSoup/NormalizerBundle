<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Normalize;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Annotation\Translate;
use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Model\ObjectBag;
use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class MethodNormalizer extends AbstractNormalizer
{
    public function __construct(
        ClassExtractor $classExtractor,
        TranslatorInterface $translator,
        AnnotationExtractor $annotationExtractor,
        private readonly MethodExtractor $methodExtractor,
    ) {
        parent::__construct($classExtractor, $translator, $annotationExtractor);
    }

    /**
     * @throws BosNormalizerException
     * @throws \ReflectionException
     */
    public function normalize(
        Normalizer $sharedNormalizer,
        ObjectBag $objectBag,
        ?string $group,
    ): array {
        $object = $objectBag->getObject();
        $objectName = $objectBag->getObjectName();

        $this->sharedNormalizer = $sharedNormalizer;
        $this->group = $group;

        $this->processedDepthObjects[$objectName] = $this->processedDepth;

        $normalizedMethods = [];
        $classAnnotation = $this->getClassAnnotation($object);

        $classMethods = $this->methodExtractor->getMethods($object);
        foreach ($classMethods as $classMethod) {
            $methodAnnotations = $this->annotationExtractor->getAnnotationsForMethod(Normalize::class, $classMethod);
            if (empty($methodAnnotations)) {
                continue;
            }

            $normalizedMethod = $this->normalizeMethod(
                $object,
                $classMethod,
                $methodAnnotations,
                $classAnnotation
            );
            if (empty($normalizedMethod)) {
                continue;
            }

            $normalizedMethods[] = $normalizedMethod;
        }

        return $normalizedMethods;
    }

    /**
     * @throws BosNormalizerException
     * @throws \ReflectionException
     */
    private function normalizeMethod(
        object $object,
        \ReflectionMethod $method,
        array $methodAnnotations,
        ?Normalize $classAnnotation,
    ): array {
        $normalizedProperties = [];

        /** @var Normalize $methodAnnotation */
        foreach ($methodAnnotations as $methodAnnotation) {
            if (!$methodAnnotation->isGroupValidForConstruct($this->group)) {
                continue;
            }

            $translateAnnotations = $this->annotationExtractor->getAnnotationsForMethod(Translate::class, $method);
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
     * Returns values for methods with the annotation property 'type'.
     *
     * @throws \ReflectionException
     * @throws BosNormalizerException
     */
    private function getValueForMethodWithType(
        object $object,
        \ReflectionMethod $method,
        mixed $methodValue,
        Normalize $methodAnnotation,
        string $annotationMethodType,
    ): string|int|array|null {
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
     * Returns values for methods with annotation type 'datetime'.
     *
     * @throws BosNormalizerException
     * @throws \ReflectionException
     */
    private function getValueForMethodWithDateTime(object $object, \ReflectionMethod $method, Normalize $methodAnnotation): ?string
    {
        $methodName = $method->getName();
        $methodValue = null;

        $annotationPropertyCallback = $methodAnnotation->getCallback();
        if (!empty($annotationPropertyCallback)) {
            $this->callbackException($methodName);
        } else {
            $methodValue = $method->invoke($object);
        }

        if ($methodValue instanceof \DateTimeInterface) {
            return $methodValue->format($methodAnnotation->getFormat());
        }

        return null;
    }

    /**
     * Returns values for methods with annotation type 'object'.
     *
     * @param mixed $methodValue
     *
     * @throws BosNormalizerException
     * @throws \ReflectionException
     */
    private function getValueForMethodWithTypeObject(object $object, \ReflectionMethod $method, $methodValue, Normalize $propertyAnnotation): string|array|int|null
    {
        $methodName = $method->getName();

        if ($this->hasMaxDepth()) {
            return $this->getValueForMaxDepth($methodValue);
        }
        ++$this->processedDepth;

        $annotationCallback = $propertyAnnotation->getCallback();
        if (!empty($annotationCallback)) {
            $this->callbackException($methodName);
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
     * @throws BosNormalizerException
     */
    private function callbackException(string $methodName): void
    {
        throw new BosNormalizerException(sprintf('A callback is set on method %s. Callbacks are not allowed on methods.', $methodName));
    }
}
