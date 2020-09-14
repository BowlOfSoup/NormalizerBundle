<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Tests\ArraySubset;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class PropertyExtractorTest extends TestCase
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor|\PHPUnit\Framework\MockObject\MockObject */
    private $propertyExtractor;

    protected function setUp(): void
    {
        $this->propertyExtractor = $this
            ->getMockBuilder(PropertyExtractor::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    /**
     * @testdox Try to get methods from a non-object.
     */
    public function testGetMethodsForNothing(): void
    {
        $result = $this->propertyExtractor->getProperties('foo');

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }

    /**
     * @testdox Get all properties of a class.
     */
    public function testGetProperties()
    {
        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $stubPropertyExtractor */
        $stubPropertyExtractor = $this
            ->getMockBuilder(PropertyExtractor::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $someClass = new SomeClass();
        $properties = $stubPropertyExtractor->getProperties($someClass);
        $this->assertCount(5, $properties);

        $property = $properties[0];
        $this->assertSame('property32', $property->getName());
        $this->assertInstanceOf(\ReflectionProperty::class, $property);
        $property->setAccessible(true);
        $this->assertSame(123, $property->getValue($someClass));

        $property = $properties[1];
        $this->assertSame('property53', $property->getName());
        $this->assertInstanceOf(\ReflectionProperty::class, $property);
        $this->assertSame('string', $property->getValue($someClass));

        $property = $properties[2];
        $this->assertSame('property76', $property->getName());
        $this->assertInstanceOf(\ReflectionProperty::class, $property);

        $property = $properties[3];
        $this->assertSame('property2', $property->getName());
        $this->assertInstanceOf(\ReflectionProperty::class, $property);
        $property->setAccessible(true);
        $this->assertSame([], $property->getValue($someClass));

        $property = $properties[4];
        $this->assertSame('property1', $property->getName());
        $this->assertInstanceOf(\ReflectionProperty::class, $property);
        $property->setAccessible(true);
        $this->assertSame('string', $property->getValue($someClass));
    }

    /**
     * @testdox Extracting property annotations.
     */
    public function testExtractPropertyAnnotations(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();
        $properties = $this->propertyExtractor->getProperties($someClass);

        $annotationResult = [$annotation];

        /** @var \Doctrine\Common\Annotations\AnnotationReader $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPropertyAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getPropertyAnnotations')
            ->with($this->equalTo($properties[0]))
            ->willReturn($annotationResult);

        $propertyExtractor = new PropertyExtractor($mockAnnotationReader);
        $result = $propertyExtractor->extractPropertyAnnotations($properties[0], get_class($annotation));

        ArraySubset::assert([$annotation], $result);
    }

    /**
     * @testdox Get a value for a property by specifying method.
     */
    public function testGetPropertyValueByMethod(): void
    {
        $someClass = new SomeClass();
        $result = $this->propertyExtractor->getPropertyValueByMethod($someClass, 'getProperty32');

        $this->assertSame(123, $result);
    }

    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetPropertyValueByMethodNoMethodAvailable(): void
    {
        $someClass = new SomeClass();
        $result = $this->propertyExtractor->getPropertyValueByMethod($someClass, 'getProperty53');

        $this->assertNull($result);
    }

    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetId(): void
    {
        $someClass = new SomeClass();
        $result = $this->propertyExtractor->getId($someClass);

        $this->assertSame(777, $result);
    }
}
