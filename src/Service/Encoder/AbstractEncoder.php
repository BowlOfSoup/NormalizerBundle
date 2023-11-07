<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;

abstract class AbstractEncoder implements EncoderInterface
{
    /** @var string|null */
    protected $wrapElement = null;

    public function setWrapElement(string $wrapElement): void
    {
        $this->wrapElement = $wrapElement;
    }

    public function populateFromAnnotation(Serialize $serializeAnnotation): void
    {
        $this->wrapElement = $serializeAnnotation->getWrapElement();
    }
}
