<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertyNormalizeMethod;
use BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertyNormalizeProperty;
use BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertySerialize;
use BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertyTranslate;
use BowlOfSoup\NormalizerBundle\Tests\SerializerTestTrait;
use Doctrine\Common\Annotations\AnnotationException;
use PHPUnit\Framework\TestCase;

class UnknownPropertyTest extends TestCase
{
    use SerializerTestTrait;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalizer */
    private $normalizer;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Serializer */
    private $serializer;

    public function setUp(): void
    {
        $this->normalizer = $this->getNormalizer();
        $this->serializer = $this->getSerializer();
    }

    public function testNormalizeWithAnUnknownPropertyInTheAnnotation(): void
    {
        $unknownPropertyObject = new UnknownPropertyNormalizeProperty();
        $unknownPropertyObject->setName('foo');

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('[Creation Error] An error occurred while instantiating the annotation @Bos\Normalize declared on property BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertyNormalizeProperty::$name: "Property "asdsad" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize" is unknown.');

        $this->normalizer->normalize($unknownPropertyObject, 'default');
    }

    public function testNormalizeWithAnUnknownPropertyInTheAnnotationForAMethod(): void
    {
        $unknownPropertyObject = new UnknownPropertyNormalizeMethod();
        $unknownPropertyObject->setName('foo');

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('[Creation Error] An error occurred while instantiating the annotation @Bos\Normalize declared on method BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertyNormalizeMethod::getName(): "Property "asdsad" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize" is unknown.');

        $this->normalizer->normalize($unknownPropertyObject, 'default');
    }

    public function testSerializeWithAnUnknownPropertyInTheAnnotation(): void
    {
        $unknownPropertyObject = new UnknownPropertySerialize();
        $unknownPropertyObject->setName('foo');

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('[Creation Error] An error occurred while instantiating the annotation @Bos\Serialize declared on class BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertySerialize: "Property "wrappElement" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Serialize" is unknown.');

        $this->serializer->serialize($unknownPropertyObject, EncoderFactory::TYPE_XML, 'default');
    }

    public function testNormalizeAndTranslateWithAnUnknownPropertyInTheAnnotation(): void
    {
        $unknownPropertyObject = new UnknownPropertyTranslate();

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('[Creation Error] An error occurred while instantiating the annotation @Bos\Translate declared on property BowlOfSoup\NormalizerBundle\Tests\assets\UnknownPropertyTranslate::$name: "Property "groupp" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Translate" is unknown.');

        $this->normalizer->normalize($unknownPropertyObject, 'default');
    }
}
