<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use stdClass;

class ClassExtractorTest extends PHPUnit_Framework_TestCase
{
    /** @var string */
    const ANNOTATION_NORMALIZE = 'BowlOfSoup\NormalizerBundle\Annotation\Normalize';

    /**
     * @testdox Extracting class annotations.
     */
    public function testExtractClassAnnotation()
    {
        $object = new stdClass();
        $reflectedClass = new ReflectionClass($object);

        $mockAnnotationReader = $this
            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassAnnotation'))
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getClassAnnotation')
            ->with($this->equalTo($reflectedClass), $this->equalTo(static::ANNOTATION_NORMALIZE));

        $classExtractor = new ClassExtractor($mockAnnotationReader);
        $classExtractor->extractClassAnnotation($object, static::ANNOTATION_NORMALIZE);
    }

    /**
     * @testdox Get all properties of a class.
     */
    public function testGetProperties()
    {
        $stubClassExtractor = $this
            ->getMockBuilder('BowlOfSoup\NormalizerBundle\Service\ClassExtractor')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $someClass = new SomeClass();
        $properties = $stubClassExtractor->getProperties($someClass);
        $this->assertSame(4, count($properties));

        $property = $properties[0];
        $this->assertSame('property32', $property->getName());
        $this->assertInstanceOf('\ReflectionProperty', $property);
        $property->setAccessible(true);
        $this->assertSame(123, $property->getValue($someClass));

        $property = $properties[1];
        $this->assertSame('property53', $property->getName());
        $this->assertInstanceOf('\ReflectionProperty', $property);
        $this->assertSame('string', $property->getValue($someClass));

        $property = $properties[2];
        $this->assertSame('property2', $property->getName());
        $this->assertInstanceOf('\ReflectionProperty', $property);
        $property->setAccessible(true);
        $this->assertSame(array(), $property->getValue($someClass));

        $property = $properties[3];
        $this->assertSame('property1', $property->getName());
        $this->assertInstanceOf('\ReflectionProperty', $property);
        $property->setAccessible(true);
        $this->assertSame('string', $property->getValue($someClass));
    }
}
