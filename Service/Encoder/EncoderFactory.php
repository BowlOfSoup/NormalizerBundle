<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;

class EncoderFactory
{
    /** @var string */
    const TYPE_JSON = 'json';

    /** @var string */
    const TYPE_XML = 'xml';

    /**
     * @param string $type
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     *
     * @return EncoderInterface
     */
    public static function getEncoder($type)
    {
        switch ($type) {
            case static::TYPE_JSON :
                return new EncoderJson();
            case static::TYPE_XML :
                return new EncoderXml();
            default :
                throw new BosSerializerException('Unknown encoder.');
                break;
        }
    }
}
