<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderInterface;

class Serializer
{
    /** @var ClassExtractor */
    private $classExtractor;

    /** @var Normalizer */
    private $normalizer;

    /**
     * @param ClassExtractor $classExtractor
     * @param Normalizer     $normalizer
     */
    public function __construct(
        ClassExtractor $classExtractor,
        Normalizer $normalizer
    ) {
        $this->classExtractor = $classExtractor;
        $this->normalizer = $normalizer;
    }

    /**
     * @param mixed                   $value
     * @param string|EncoderInterface $encoding
     * @param string|null             $group
    *
     * @return string
    */
    public function serialize($value, $encoding, $group = null)
    {
        $serializeAnnotation = null;

        if (is_object($value)) {
            $serializeAnnotation = $this->getClassAnnotation($value, $group);
            $value = $this->normalizer->normalize($value, $group);
        }

        $encoder = $this->getEncoder($encoding);
        if (null !== $serializeAnnotation) {
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
     * @param object      $object
     * @param string|null $group
     *
     * @return Serialize|null
     */
    private function getClassAnnotation($object, $group)
    {
        $classAnnotations = $this->classExtractor->extractClassAnnotations($object, new Serialize(array()));
        if (empty($classAnnotations)) {
            return null;
        }

        /** @var Serialize $classAnnotation */
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation->isGroupValidForProperty($group)) {
                return $classAnnotation;
            }
        }

        return null;
    }

    /**
     * @param string|EncoderInterface $encoding
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     *
     * @return EncoderInterface
     */
    private function getEncoder($encoding)
    {
        if ($encoding instanceof EncoderInterface) {
            return $encoding;
        }

        $encoder = EncoderFactory::getEncoder($encoding);
        if (null === $encoder) {
            throw new BosNormalizerException('Can not encode. Given encoder type does not exist.');
        }

        return $encoder;
    }
}
