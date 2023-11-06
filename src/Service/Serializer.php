<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderInterface;
use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;

class Serializer
{
    private AnnotationExtractor $annotationExtractor;
    private Normalizer $normalizer;

    public function __construct(
        AnnotationExtractor $annotationExtractor,
        Normalizer $normalizer
    ) {
        $this->annotationExtractor = $annotationExtractor;
        $this->normalizer = $normalizer;
    }

    /**
     * @param mixed $value
     * @param string|\BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderInterface $encoding
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     * @throws \ReflectionException
     */
    public function serialize($value, $encoding, string $group = null): string
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

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\Serialize $classAnnotation */
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation->isGroupValidForConstruct($group)) {
                return $classAnnotation;
            }
        }

        return null;
    }

    /**
     * @param string|\BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderInterface $encoding
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     */
    private function getEncoder($encoding): EncoderInterface
    {
        if ($encoding instanceof EncoderInterface) {
            return $encoding;
        }

        return EncoderFactory::getEncoder($encoding);
    }
}
