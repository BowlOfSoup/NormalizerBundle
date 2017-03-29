<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
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
        $annotation = new Normalize(array());
        $annotationResult = array($annotation);

        $someClass = new SomeClass();
        $reflectedClass = new ReflectionClass($someClass);

        $mockAnnotationReader = $this
            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
            ->disableOriginalConstructor()
            ->setMethods(array('getClassAnnotations'))
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getClassAnnotations')
            ->with($this->equalTo($reflectedClass))
            ->will($this->returnValue($annotationResult));

        $classExtractor = new ClassExtractor($mockAnnotationReader);
        $classExtractor->extractClassAnnotations($someClass, static::ANNOTATION_NORMALIZE);
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
        $this->assertSame(5, count($properties));

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
        $this->assertSame('property76', $property->getName());
        $this->assertInstanceOf('\ReflectionProperty', $property);

        $property = $properties[3];
        $this->assertSame('property2', $property->getName());
        $this->assertInstanceOf('\ReflectionProperty', $property);
        $property->setAccessible(true);
        $this->assertSame(array(), $property->getValue($someClass));

        $property = $properties[4];
        $this->assertSame('property1', $property->getName());
        $this->assertInstanceOf('\ReflectionProperty', $property);
        $property->setAccessible(true);
        $this->assertSame('string', $property->getValue($someClass));
    }
}
