<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;

class EncoderFactory
{
    /** @var string */
    public const TYPE_JSON = 'json';

    /** @var string */
    public const TYPE_XML = 'xml';

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     */
    public static function getEncoder(string $type): EncoderInterface
    {
        switch ($type) {
            case static::TYPE_JSON:
                return new EncoderJson();
            case static::TYPE_XML:
                return new EncoderXml();
            default:
                throw new BosSerializerException('Unknown encoder type.');
                break;
        }
    }
}
