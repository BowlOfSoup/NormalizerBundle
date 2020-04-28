<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;

interface EncoderInterface
{
    public function getType(): string;

    /**
     * @param array|mixed $value
     */
    public function encode($value): ?string;

    public function populateFromAnnotation(Serialize $serializeAnnotation): void;
}
