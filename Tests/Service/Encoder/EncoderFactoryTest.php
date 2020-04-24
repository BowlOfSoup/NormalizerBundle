<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderXml;
use PHPUnit\Framework\TestCase;

class EncoderFactoryTest extends TestCase
{
    /**
     * @testdox Factory returns correct encoder.
     */
    public function testManufacturingEncoder(): void
    {
        $this->assertInstanceOf(
            EncoderJson::class,
            EncoderFactory::getEncoder(EncoderFactory::TYPE_JSON)
        );
        $this->assertInstanceOf(
            EncoderXml::class,
            EncoderFactory::getEncoder(EncoderFactory::TYPE_XML)
        );
    }

    public function testUnknownEncoder(): void
    {
        $this->expectException(BosSerializerException::class);
        $this->expectExceptionMessage('Unknown encoder type.');

        EncoderFactory::getEncoder('something');
    }
}
