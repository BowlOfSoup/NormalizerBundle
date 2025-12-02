<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;

interface EncoderInterface
{
    public function getType(): string;

    public function encode(mixed $value): ?string;

    public function populateFromAnnotation(Serialize $serializeAnnotation): void;
}
