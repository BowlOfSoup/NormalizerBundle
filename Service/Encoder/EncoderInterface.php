<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Exception\NormalizerBundleException;

interface EncoderInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function encode($value);

    /**
     * @param Serialize $serializeAnnotation
     */
    public function populateFromAnnotation(Serialize $serializeAnnotation);
}