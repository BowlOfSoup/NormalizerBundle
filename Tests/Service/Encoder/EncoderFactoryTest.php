<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use PHPUnit_Framework_TestCase;

class ClassExtractorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Factory returns correct encoder.
     */
    public function testManufactoringEncoder()
    {
        $this->assertInstanceOf(
            'BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson',
            EncoderFactory::getEncoder(EncoderFactory::TYPE_JSON)
        );
        $this->assertInstanceOf(
            'BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderXml',
            EncoderFactory::getEncoder(EncoderFactory::TYPE_XML)
        );
    }

    /**
     * @testdox Unknown encoder.
     *
     * @expectedException \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     * @expectedExceptionMessage Unknown encoder.
     */
    public function testUnknownEncoder()
    {
        EncoderFactory::getEncoder('something');
    }
}
