<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderXml;
use PHPUnit_Framework_TestCase;

class EncoderXmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Encoder is of correct type.
     */
    public function testType()
    {
        $encoderXml = new EncoderXml();

        $this->assertSame(EncoderFactory::TYPE_XML, $encoderXml->getType());
    }

    /**
     * @testdox Encoder encodes successfully.
     */
    public function testSuccess()
    {
        $encoderXml = new EncoderXml();
        $encoderXml->setWrapElement('data');

        $normalizedString = array(
            'id' => 123,
            'name_value' => 'Bowl',
            'surName' => 'Of Soup',
            'initials' => null,
            'dateOfBirth' => '1980-01-01',
            'dateOfRegistration' => 'Apr. 2015',
            'addresses' => array(
                array(
                    'street' => 'Dummy Street',
                    'number' => null,
                    'postalCode' => null,
                    'city' => 'The City Is: Amsterdam',
                ),
                array(
                    'street' => null,
                    'number' => 4,
                    'postalCode' => '1234AB',
                    'city' => 'The City Is: ',
                ),
            ),
        );

        $expected = '<data><id>123</id><name_value>Bowl</name_value><surName>Of Soup</surName><initials/><dateOfBirth>1980-01-01</dateOfBirth><dateOfRegistration>Apr. 2015</dateOfRegistration><addresses><item0><street>Dummy Street</street><number/><postalCode/><city>The City Is: Amsterdam</city></item0><item1><street/><number>4</number><postalCode>1234AB</postalCode><city>The City Is: </city></item1></addresses></data>';
        if (version_compare(PHP_VERSION, '7.0.0') < 0) {
           $expected = '<data><id>123</id><name_value>Bowl</name_value><surName>Of Soup</surName><initials></initials><dateOfBirth>1980-01-01</dateOfBirth><dateOfRegistration>Apr. 2015</dateOfRegistration><addresses><item0><street>Dummy Street</street><number></number><postalCode></postalCode><city>The City Is: Amsterdam</city></item0><item1><street></street><number>4</number><postalCode>1234AB</postalCode><city>The City Is: </city></item1></addresses></data>';
        }

        $this->assertContains($expected, $encoderXml->encode($normalizedString));
    }

    /**
     * @testdox Encoder encodes with error.
     *
     * @expectedException \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     * @expectedExceptionMessage Opening and ending tag mismatch: titles line 1 and title
     */
    public function testError()
    {
        $encoderXml = new EncoderXml();

        $reflectionClass = new \ReflectionClass($encoderXml);
        $reflectionMethod = $reflectionClass->getMethod('getError');
        $reflectionMethod->setAccessible(true);

        $reflectionMethod->invokeArgs($encoderXml, array('<movies><movie><titles>Faulty XML</title></movie></movies>'));
    }

    /**
     * @testdox Populate option from annotation.
     */
    public function testPopulate()
    {
        $serializeAnnotation = new Serialize(
            array(
                'wrapElement' => 'test',
            )
        );

        $encoderXml = new EncoderXml();
        $encoderXml->populateFromAnnotation($serializeAnnotation);

        $result = $encoderXml->encode(
            array(
                'id' => 123,
            )
        );

        $this->assertContains('<test><id>123</id></test>', $result);
    }
}
