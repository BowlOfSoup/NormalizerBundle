<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

class EncoderFactory
{
    /** @var string */
    const TYPE_JSON = 'json';

    /** @var string */
    const TYPE_XML = 'xml';

    /** @var string */
    const TYPE_YAML = 'yaml';

    /**
     * @param string $type
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
            case static::TYPE_YAML :
                return new EncoderYaml();
        }
    }
}
