<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson;
use PHPUnit\Framework\TestCase;

class EncoderJsonTest extends TestCase
{
    /**
     * @testdox Encoder is of correct type.
     */
    public function testType(): void
    {
        $encoderJson = new EncoderJson();

        $this->assertSame(EncoderFactory::TYPE_JSON, $encoderJson->getType());
    }

    /**
     * @testdox Encoder encodes successfully.
     */
    public function testSuccess(): void
    {
        $encoderJson = new EncoderJson();
        $encoderJson->setWrapElement('data');
        $encoderJson->setOptions(JSON_ERROR_UTF8);

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
     * @testdox Encoder encodes with error.
     */
    public function testError(): void
    {
        $this->expectException(BosSerializerException::class);
        $this->expectExceptionMessage('Error when encoding JSON: Recursion detected');

        $o = new \stdClass();
        $o->arr = [];
        $o->arr[] = $o;

        $encoderJson = new EncoderJson();
        $encoderJson->encode($o);
    }

    /**
     * @testdox json_last_error_msg does not exists.
     */
    public function testJsonLastErrorMsgMethodDoesNotExists(): void
    {
        $normalizedData = [
            'id' => 123,
        ];

        $mockBuilder = $this
            ->getMockBuilder(EncoderJson::class)
            ->disableOriginalConstructor()
            ->setMethods(['jsonLastErrorMsgExists']);

        /** @var \BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson $encoderJson */
        $encoderJson = $mockBuilder->getMock();
        $encoderJson
            ->expects($this->any())
            ->method('jsonLastErrorMsgExists')
            ->willReturn(false);

        $this->assertSame('{"id":123}', $encoderJson->encode($normalizedData));
    }

    /**
     * @testdox Populate option from annotation.
     */
    public function testPopulate(): void
    {
        $serializeAnnotation = new Serialize(
            [
                'wrapElement' => 'test',
            ]
        );

        $encoderJson = new EncoderJson();
        $encoderJson->populateFromAnnotation($serializeAnnotation);

        $result = $encoderJson->encode(
            [
                'id' => 123,
            ]
        );

        $this->assertSame('{"test":{"id":123}}', $result);
    }
}
