<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson;
use PHPUnit_Framework_TestCase;

class EncoderJsonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Encoder is of correct type.
     */
    public function testType()
    {
        $encoderJson = new EncoderJson();

        $this->assertSame(EncoderFactory::TYPE_JSON, $encoderJson->getType());
    }

    /**
     * @testdox Encoder encodes sucessfully.
     */
    public function testSuccess()
    {
        $encoderJson = new EncoderJson();
        $encoderJson->setWrapElement('data');
        $encoderJson->setOptions(JSON_ERROR_UTF8);

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

        $result = $encoderJson->encode($normalizedString);

        $this->assertSame(
            '{"data":{"id":123,"name_value":"Bowl","surName":"Of Soup","initials":null,' .
            '"dateOfBirth":"1980-01-01","dateOfRegistration":"Apr. 2015","addresses":[{"street":"Dummy Street",' .
            '"number":null,"postalCode":null,"city":"The City Is: Amsterdam"},{"street":null,"number":4,' .
            '"postalCode":"1234AB","city":"The City Is: "}]}}',
            $result
        );
    }

    /**
     * @testdox Encoder encodes sucessfully.
     *
     * @expectedException \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @expectedExceptionMessage Error when encoding JSON: Recursion detected
     */
    public function testError()
    {
        $o = new \stdClass();
        $o->arr = array();
        $o->arr[] = $o;

        $encoderJson = new EncoderJson();
        $encoderJson->encode($o);
    }

    /**
     * @testdox Populate option from annotation.
     */
    public function testPopulate()
    {
        $serializeAnnotation = new Serialize(array(
            'wrapElement' => 'test',
        ));

        $encoderJson = new EncoderJson();
        $encoderJson->populateFromAnnotation($serializeAnnotation);

        $result = $encoderJson->encode(array(
            'id' => 123,
        ));

        $this->assertSame(
            '{"test":{"id":123}}',
            $result
        );
    }
}
