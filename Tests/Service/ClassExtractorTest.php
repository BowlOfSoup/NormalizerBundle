<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Service\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use PHPUnit\Framework\TestCase;

class ClassExtractorTest extends TestCase
{
//    /** @var string */
//    private const ANNOTATION_NORMALIZE = Normalize::class;
//
//    /**
//     * @testdox Extracting class annotations.
//     *
//     * @throws \ReflectionException
//     */
//    public function testExtractClassAnnotation(): void
//    {
//        $annotation = new Normalize([]);
//        $annotationResult = [$annotation];
//
//        $someClass = new SomeClass();
//        $reflectedClass = new \ReflectionClass($someClass);
//
//        /** @var \Doctrine\Common\Annotations\AnnotationReader $mockAnnotationReader */
//        $mockAnnotationReader = $this
//            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
//            ->disableOriginalConstructor()
//            ->setMethods(['getClassAnnotations'])
//            ->getMock();
//        $mockAnnotationReader
//            ->expects($this->once())
//            ->method('getClassAnnotations')
//            ->with($this->equalTo($reflectedClass))
//            ->willReturn($annotationResult);
//
//        $classExtractor = new ClassExtractor($mockAnnotationReader);
//        $classExtractor->extractClassAnnotations($someClass, static::ANNOTATION_NORMALIZE);
//    }
//
//    /**
//     * @testdox Extracting class annotations, but no class (object) given.
//     */
//    public function testExtractClassAnnotationNoClassGiven(): void
//    {
//        /** @var \Doctrine\Common\Annotations\AnnotationReader $mockAnnotationReader */
//        $mockAnnotationReader = $this
//            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
//            ->disableOriginalConstructor()
//            ->getMock();
//
//        $classExtractor = new ClassExtractor($mockAnnotationReader);
//
//        $this->assertIsArray($classExtractor->extractClassAnnotations([], static::ANNOTATION_NORMALIZE));
//    }
//
//    /**
//     * @testdox Get all properties of a class.
//     */
//    public function testGetProperties()
//    {
//        /** @var \BowlOfSoup\NormalizerBundle\Service\ClassExtractor $stubClassExtractor */
//        $stubClassExtractor = $this
//            ->getMockBuilder(ClassExtractor::class)
//            ->disableOriginalConstructor()
//            ->setMethods(null)
//            ->getMock();
//
//        $someClass = new SomeClass();
//        $properties = $stubClassExtractor->getProperties($someClass);
//        $this->assertCount(5, $properties);
//
//        $property = $properties[0];
//        $this->assertSame('property32', $property->getName());
//        $this->assertInstanceOf(\ReflectionProperty::class, $property);
//        $property->setAccessible(true);
//        $this->assertSame(123, $property->getValue($someClass));
//
//        $property = $properties[1];
//        $this->assertSame('property53', $property->getName());
//        $this->assertInstanceOf(\ReflectionProperty::class, $property);
//        $this->assertSame('string', $property->getValue($someClass));
//
//        $property = $properties[2];
//        $this->assertSame('property76', $property->getName());
//        $this->assertInstanceOf(\ReflectionProperty::class, $property);
//
//        $property = $properties[3];
//        $this->assertSame('property2', $property->getName());
//        $this->assertInstanceOf(\ReflectionProperty::class, $property);
//        $property->setAccessible(true);
//        $this->assertSame([], $property->getValue($someClass));
//
//        $property = $properties[4];
//        $this->assertSame('property1', $property->getName());
//        $this->assertInstanceOf(\ReflectionProperty::class, $property);
//        $property->setAccessible(true);
//        $this->assertSame('string', $property->getValue($someClass));
//    }
}
