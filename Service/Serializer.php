<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Exception\NormalizerBundleException;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderInterface;

class Serializer
{
    /** @var ClassExtractor */
    private $classExtractor;

    /** @var Normalizer */
    private $normalizer;

    /** @var EncoderInterface */
    private $encoderProperties;

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
     * @param EncoderInterface $encoderType
     */
    public function setEncoderProperties(EncoderInterface $encoderType)
    {
        $this->encoderProperties = $encoderType;
    }

    /**
     * @param mixed       $value
     * @param string|null $type
     * @param string|null $group
     *
     * @return string
     */
    public function serialize($value, $type = null, $group = null)
    {
        $serializeAnnotation = null;

        if (is_object($value)) {
            $serializeAnnotation = $this->getClassAnnotation($value, $group);
            $value = $this->normalizer->normalize($value, $group);
        }

        $encoder = $this->getEncoder($type);
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

        /** @var \BowlOfSoup\NormalizerBundle\Annotation\AbstractAnnotation $classAnnotation */
        foreach ($classAnnotations as $classAnnotation) {
            if ($classAnnotation->isGroupValidForProperty($group)) {
                return $classAnnotation;
            }
        }

        return null;
    }

    /**
     * @param string|null $encodingType
     *
     * @throws NormalizerBundleException
     *
     * @return EncoderInterface
     */
    private function getEncoder($encodingType = null)
    {
        if (null === $this->encoderProperties || $encodingType !== $this->encoderProperties->getType()) {
            throw new NormalizerBundleException('Can not encode value, ' .
                'encoder properties type and given encoder type do not match.');
        }

        $encoder = EncoderFactory::getEncoder($encodingType);
        if (null === $encoder) {
            throw new NormalizerBundleException('Can not encode value, given encoder type does not exist.');
        }

        return $encoder;
    }
}
