<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderXml;
use BowlOfSoup\NormalizerBundle\Tests\assets\Social;
use PHPUnit\Framework\TestCase;

class EncoderXmlTest extends TestCase
{
    /**
     * @testdox Encoder is of correct type.
     */
    public function testType(): void
    {
        $encoderXml = new EncoderXml();

        $this->assertSame(EncoderFactory::TYPE_XML, $encoderXml->getType());
    }

    /**
     * @testdox Encoder, encodes successfully.
     */
    public function testSuccess()
    {
        $encoderXml = new EncoderXml();
        $encoderXml->setWrapElement('data');

        $normalizedString = [
            'id' => 123,
            'name_value' => 'Bowl',
            'surName' => 'Of Soup',
            'initials' => null,
            'dateOfBirth' => '1980-01-01',
            'dateOfRegistration' => 'Apr. 2015',
            'addresses' => [
                [
                    'street' => 'Dummy Street',
                    'number' => null,
                    'postalCode' => null,
                    'city' => 'The City Is: Amsterdam',
                ],
                [
                    'street' => null,
                    'number' => 4,
                    'postalCode' => '1234AB',
                    'city' => 'The City Is: ',
                ],
            ],
        ];

        $expected = '<data><id>123</id><name_value>Bowl</name_value><surName>Of Soup</surName><initials></initials><dateOfBirth>1980-01-01</dateOfBirth><dateOfRegistration>Apr. 2015</dateOfRegistration><addresses><item0><street>Dummy Street</street><number></number><postalCode></postalCode><city>The City Is: Amsterdam</city></item0><item1><street></street><number>4</number><postalCode>1234AB</postalCode><city>The City Is: </city></item1></addresses></data>';

        $this->assertStringContainsString($this->flatten($expected), $this->flatten($encoderXml->encode($normalizedString)));
    }

    /**
     * @testdox Encoder, only accepting arrays.
     */
    public function testNoArrayValue(): void
    {
        $encoderXml = new EncoderXml();
        $encoderXml->setWrapElement('data');

        $this->assertNull($encoderXml->encode(new Social()));
    }

    /**
     * @testdox Encoder, exception in loop.
     */
    public function testExceptionInXmlLoop(): void
    {
        $this->expectException(BosSerializerException::class);
        $this->expectExceptionMessage('Error when encoding XML: Dummy Message');

        $normalizedData = [
            'id' => 123,
        ];

        $mockBuilder = $this
            ->getMockBuilder(EncoderXml::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['arrayToXml']);
        $encoderXml = $mockBuilder->getMock();
        $encoderXml
            ->expects($this->any())
            ->method('arrayToXml')
            ->will($this->throwException(new \Exception('Dummy Message')));

        $encoderXml->setWrapElement('data');

        $expected = '<data><id>123</id><name_value>Bowl</name_value><surName>Of Soup</surName><initials></initials><dateOfBirth>1980-01-01</dateOfBirth><dateOfRegistration>Apr. 2015</dateOfRegistration><addresses><item0><street>Dummy Street</street><number></number><postalCode></postalCode><city>The City Is: Amsterdam</city></item0><item1><street></street><number>4</number><postalCode>1234AB</postalCode><city>The City Is: </city></item1></addresses></data>';
        $this->assertStringContainsString($expected, $encoderXml->encode($normalizedData));
    }

    /**
     * @testdox Encoder, encodes with error.
     *
     * @throws \ReflectionException
     */
    public function testError(): void
    {
        $this->expectException(BosSerializerException::class);
        $this->expectExceptionMessage('Opening and ending tag mismatch: titles line 1 and title');

        $encoderXml = new EncoderXml();

        $reflectionClass = new \ReflectionClass($encoderXml);
        $reflectionMethod = $reflectionClass->getMethod('getError');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invokeArgs($encoderXml, ['<movies><movie><titles>Faulty XML</title></movie></movies>']);
    }

    /**
     * @testdox Populate encoder option from annotation.
     */
    public function testPopulate(): void
    {
        $serializeAnnotation = new Serialize(
            [
                'wrapElement' => 'test',
            ]
        );

        $encoderXml = new EncoderXml();
        $encoderXml->populateFromAnnotation($serializeAnnotation);

        $result = $encoderXml->encode(
            [
                'id' => 123,
            ]
        );

        $this->assertStringContainsString($this->flatten('<test><id>123</id></test>'), $this->flatten($result));
    }

    private function flatten(string $value): string
    {
        return trim(preg_replace('/\s+/', '', $value));
    }
}
