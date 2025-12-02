<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;

class EncoderFactory
{
    public const string TYPE_JSON = 'json';
    public const string TYPE_XML = 'xml';

    /**
     * @throws BosSerializerException
     */
    public static function getEncoder(string $type): EncoderInterface
    {
        return match ($type) {
            static::TYPE_JSON => new EncoderJson(),
            static::TYPE_XML => new EncoderXml(),
            default => throw new BosSerializerException('Unknown encoder type.'),
        };
    }
}
