<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;

abstract class AbstractEncoder implements EncoderInterface
{
    /** @var string */
    protected $wrapElement;

    public function setWrapElement(string $wrapElement): void
    {
        $this->wrapElement = $wrapElement;
    }

    /**
     * @inheritdoc
     */
    public function populateFromAnnotation(Serialize $serializeAnnotation): void
    {
        $this->wrapElement = $serializeAnnotation->getWrapElement();
    }
}
