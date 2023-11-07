<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use BowlOfSoup\NormalizerBundle\Service\Serializer;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use BowlOfSoup\NormalizerBundle\Tests\assets\Social;
use BowlOfSoup\NormalizerBundle\Tests\SerializerTestTrait;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    use SerializerTestTrait;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalizer */
    private $normalizer;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Serializer */
    private $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->getSerializer();
    }

    /**
     * @testdox Serialize array, with class annotation.
     */
    public function testSerializeSuccess(): void
    {
        $this->assertSame(
            '{"wrapperElement":{"addresses":null,"dateOfBirth":null,"dateOfRegistration":"Apr. 2015","hobbies":null,"id":123,"initials":null,"name_value":"Bowl Of Soup","nonValidCollectionProperty":null,"social":{"twitter":"@bos"},"surName":null,"telephoneNumbers":null,"testForNormalizingCallback":[{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},{"city":"The City Is: ","getSpecialNotesForDelivery":"some special string","number":4,"postalCode":"1234AB","street":null}],"testForNormalizingCallbackArray":["123","456","789"],"testForNormalizingCallbackObject":{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},"testForNormalizingCallbackString":"asdasd","testForProxy":null,"validCollectionPropertyWithCallback":null,"validEmptyObjectProperty":null}}',
            $this->serializer->serialize($this->getPersonObject(), EncoderFactory::TYPE_JSON, 'default')
        );
    }

    /**
     * @testdox Serialize array, with class annotation, wrong group.
     */
    public function testSerializeSuccessUnknownGroupForClassAnnotation(): void
    {
        $this->assertSame(
            '{"addresses":null}',
            $this->serializer->serialize($this->getPersonObject(), EncoderFactory::TYPE_JSON, 'maxDepthTestDepthNoIdentifier')
        );
    }

    /**
     * Serialize array, with class annotation and custom encoder settings.
     */
    public function testSerializeWithCustomEncoder(): void
    {
        $encoderJson = new EncoderJson();
        $encoderJson->setOptions(JSON_FORCE_OBJECT);

        $this->assertSame(
            '{"wrapperElement":{"addresses":null,"dateOfBirth":null,"dateOfRegistration":"Apr. 2015","hobbies":null,"id":123,"initials":null,"name_value":"Bowl Of Soup","nonValidCollectionProperty":null,"social":{"twitter":"@bos"},"surName":null,"telephoneNumbers":null,"testForNormalizingCallback":{"0":{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},"1":{"city":"The City Is: ","getSpecialNotesForDelivery":"some special string","number":4,"postalCode":"1234AB","street":null}},"testForNormalizingCallbackArray":{"0":"123","1":"456","2":"789"},"testForNormalizingCallbackObject":{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},"testForNormalizingCallbackString":"asdasd","testForProxy":null,"validCollectionPropertyWithCallback":null,"validEmptyObjectProperty":null}}',
            $this->serializer->serialize($this->getPersonObject(), $encoderJson, 'default')
        );
    }

    /**
     * @testdox Serialize array, no class annotation.
     */
    public function testSerializeNoClassAnnotation(): void
    {
        $social = new Social();
        $social->setTwitter('@bos');

        $this->assertSame(
            '<?xmlversion="1.0"encoding="UTF-8"?><data><twitter>@bos</twitter></data>',
            trim(preg_replace('/\s+/', '', $this->serializer->serialize($social, EncoderFactory::TYPE_XML, 'default')))
        );
    }

    private function getPersonObject(): Person
    {
        $person = new Person();
        $person->setId(123);
        $person->setName('Bowl Of Soup');

        $social = new Social();
        $social->setId(456);
        $social->setTwitter('@bos');

        $person->setSocial($social);

        return $person;
    }
}
