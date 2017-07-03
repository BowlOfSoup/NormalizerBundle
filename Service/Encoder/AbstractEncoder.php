<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;

abstract class AbstractEncoder implements EncoderInterface
{
    /** @var string */
    protected $wrapElement;

    /**
     * @param string $wrapElement
     */
    public function setWrapElement($wrapElement)
    {
        $this->wrapElement = $wrapElement;
    }

    /**
     * @inheritdoc
     */
    public function populateFromAnnotation(Serialize $serializeAnnotation)
    {
        $this->wrapElement = $serializeAnnotation->getWrapElement();
    }
}
