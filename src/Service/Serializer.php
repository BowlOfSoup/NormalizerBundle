<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderInterface;
use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;

class Serializer
{
    public function __construct(
        private readonly AnnotationExtractor $annotationExtractor,
        private readonly Normalizer $normalizer,
    ) {
    }

    /**
     * @throws BosNormalizerException
     * @throws BosSerializerException
     * @throws \ReflectionException
     */
    public function serialize(mixed $value, string|EncoderInterface $encoding, ?string $group = null): string
    {
        $serializeAnnotation = null;

        if (is_object($value)) {
            $serializeAnnotation = $this->getClassAnnotation($value, $group);
            $value = $this->normalizer->normalize($value, $group);
        }

        if ($serializeAnnotation && $serializeAnnotation->mustSortProperties()) {
            ArrayKeySorter::sortKeysAscRecursive($value);
        }

        $encoder = $this->getEncoder($encoding);
        if ($serializeAnnotation) {
            $encoder->populateFromAnnotation($serializeAnnotation);
        }

        return $encoder->encode($value);
    }

    /**
     * Get class annotation for specified group.
     *
     * First group entry will be used, duplicate definitions will be gracefully ignored.
     *
     * In this method, 'new Serialize(array())' is used for PHP < 5.5 support,
     * Normally we should use 'Serialize::class'
     *
     * @throws \ReflectionException
     */
    private function getClassAnnotation(object $object, ?string $group): ?Serialize
    {
        $classAnnotations = $this->annotationExtractor->getAnnotationsForClass(Serialize::class, $object);
        if (empty($classAnnotations)) {
            return null;
        }

        return array_find($classAnnotations, fn (Serialize $classAnnotation) => $classAnnotation->isGroupValidForConstruct($group));
    }

    /**
     * @throws BosSerializerException
     */
    private function getEncoder(string|EncoderInterface $encoding): EncoderInterface
    {
        if ($encoding instanceof EncoderInterface) {
            return $encoding;
        }

        return EncoderFactory::getEncoder($encoding);
    }
}
