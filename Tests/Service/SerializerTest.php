<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use BowlOfSoup\NormalizerBundle\Service\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Service\Serializer;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use BowlOfSoup\NormalizerBundle\Tests\assets\Social;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\ClassExtractor */
    private $classExtractor;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalizer */
    private $normalizer;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Serializer */
    private $serializer;

    protected function setUp(): void
    {
        $this->classExtractor = new ClassExtractor(new AnnotationReader());
        $this->normalizer = new Normalizer($this->classExtractor, new PropertyExtractor(new AnnotationReader()));
        $this->serializer = new Serializer($this->classExtractor, $this->normalizer);
    }

    /**
     * @testdox Serialize array, with class annotation.
     */
    public function testSerializeSuccess(): void
    {
        $this->assertSame(
            '{"wrapperElement":{"id":123,"name_value":"Bowl Of Soup","surName":null,"initials":null,"dateOfBirth":null,"dateOfRegistration":"Apr. 2015","addresses":null,"social":{"twitter":"@bos"},"telephoneNumbers":null,"hobbies":null,"validEmptyObjectProperty":null,"nonValidCollectionProperty":null,"validCollectionPropertyWithCallback":null,"testForNormalizingCallback":[{"street":"Dummy Street","number":null,"postalCode":null,"city":"The City Is: Amsterdam"},{"street":null,"number":4,"postalCode":"1234AB","city":"The City Is: "}],"testForNormalizingCallbackObject":{"street":"Dummy Street","number":null,"postalCode":null,"city":"The City Is: Amsterdam"},"testForNormalizingCallbackString":"asdasd","testForNormalizingCallbackArray":["123","456","789"],"testForProxy":null}}',
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
            '{"wrapperElement":{"id":123,"name_value":"Bowl Of Soup","surName":null,"initials":null,"dateOfBirth":null,"dateOfRegistration":"Apr. 2015","addresses":null,"social":{"twitter":"@bos"},"telephoneNumbers":null,"hobbies":null,"validEmptyObjectProperty":null,"nonValidCollectionProperty":null,"validCollectionPropertyWithCallback":null,"testForNormalizingCallback":{"0":{"street":"Dummy Street","number":null,"postalCode":null,"city":"The City Is: Amsterdam"},"1":{"street":null,"number":4,"postalCode":"1234AB","city":"The City Is: "}},"testForNormalizingCallbackObject":{"street":"Dummy Street","number":null,"postalCode":null,"city":"The City Is: Amsterdam"},"testForNormalizingCallbackString":"asdasd","testForNormalizingCallbackArray":{"0":"123","1":"456","2":"789"},"testForProxy":null}}',
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
