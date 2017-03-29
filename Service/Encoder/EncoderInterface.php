<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;

interface EncoderInterface
{
    /**
     * @return string
     */
    public function getType();

    /**
     * @param array|mixed $value
     *
     * @return string|null
     */
    public function encode($value);

    /**
     * @param Serialize $serializeAnnotation
     */
    public function populateFromAnnotation(Serialize $serializeAnnotation);
}
