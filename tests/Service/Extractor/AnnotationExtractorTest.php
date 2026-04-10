<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Tests\ArraySubset;
use BowlOfSoup\NormalizerBundle\Tests\assets\BrokenAnnotation;
use BowlOfSoup\NormalizerBundle\Tests\assets\BrokenAttributeClass;
use BowlOfSoup\NormalizerBundle\Tests\assets\BrokenAttributeMethod;
use BowlOfSoup\NormalizerBundle\Tests\assets\BrokenAttributeProperty;
use BowlOfSoup\NormalizerBundle\Tests\assets\BrokenClassAnnotation;
use BowlOfSoup\NormalizerBundle\Tests\assets\BrokenMethodAnnotation;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AnnotationExtractorTest extends TestCase
{
    protected const string ANNOTATION_NORMALIZE = Normalize::class;

    public function testExtractClassAnnotation(): void
    {
        $annotation = new Normalize([]);
        $annotationResult = [$annotation];

        $someClass = new SomeClass();
        $reflectedClass = new \ReflectionClass($someClass);

        /** @var AnnotationReader&MockObject $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClassAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getClassAnnotations')
            ->with($this->equalTo($reflectedClass))
            ->willReturn($annotationResult);

        $classExtractor = new AnnotationExtractor();
        $classExtractor->setAnnotationReader($mockAnnotationReader);
        $classExtractor->getAnnotationsForClass(static::ANNOTATION_NORMALIZE, $someClass);
    }

    public function testExtractClassAnnotationWithException(): void
    {
        $someClass = new SomeClass();
        $reflectedClass = new \ReflectionClass($someClass);

        /** @var AnnotationReader&MockObject $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClassAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getClassAnnotations')
            ->with($this->equalTo($reflectedClass))
            ->willThrowException(new \InvalidArgumentException('message'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass: message');

        $classExtractor = new AnnotationExtractor();
        $classExtractor->setAnnotationReader($mockAnnotationReader);
        $classExtractor->getAnnotationsForClass(static::ANNOTATION_NORMALIZE, $someClass);
    }

    public function testExtractClassAnnotationNoClassGiven(): void
    {
        /** @var AnnotationReader&MockObject $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $classExtractor = new AnnotationExtractor();
        $classExtractor->setAnnotationReader($mockAnnotationReader);
        $this->assertIsArray($classExtractor->getAnnotationsForClass(static::ANNOTATION_NORMALIZE, []));
    }

    public function testExtractMethodAnnotations(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();

        /** @var MethodExtractor&MockObject $methodExtractor */
        $methodExtractor = $this
            ->getMockBuilder(MethodExtractor::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
        $methods = $methodExtractor->getMethods($someClass);

        $annotationResult = [$annotation];

        /** @var AnnotationReader&MockObject $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethodAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->with($this->equalTo($methods[0]))
            ->willReturn($annotationResult);

        $methodExtractor = new AnnotationExtractor();
        $methodExtractor->setAnnotationReader($mockAnnotationReader);
        $result = $methodExtractor->getAnnotationsForMethod($annotation::class, $methods[0]);

        ArraySubset::assert([$annotation], $result);
    }

    public function testExtractMethodAnnotationsWithException(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();

        /** @var MethodExtractor&MockObject $methodExtractor */
        $methodExtractor = $this
            ->getMockBuilder(MethodExtractor::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
        $methods = $methodExtractor->getMethods($someClass);

        /** @var AnnotationReader&MockObject $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMethodAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->with($this->equalTo($methods[0]))
            ->willThrowException(new \InvalidArgumentException('message'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass (getProperty32): message');

        $methodExtractor = new AnnotationExtractor();
        $methodExtractor->setAnnotationReader($mockAnnotationReader);
        $methodExtractor->getAnnotationsForMethod($annotation::class, $methods[0]);
    }

    public function testExtractPropertyAnnotations(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();

        /** @var PropertyExtractor&MockObject $propertyExtractor */
        $propertyExtractor = $this
            ->getMockBuilder(PropertyExtractor::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
        $properties = $propertyExtractor->getProperties($someClass);

        $annotationResult = [$annotation];

        /** @var AnnotationReader&MockObject $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPropertyAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getPropertyAnnotations')
            ->with($this->equalTo($properties[0]))
            ->willReturn($annotationResult);

        $propertyExtractor = new AnnotationExtractor();
        $propertyExtractor->setAnnotationReader($mockAnnotationReader);
        $result = $propertyExtractor->getAnnotationsForProperty($annotation::class, $properties[0]);

        ArraySubset::assert([$annotation], $result);
    }

    public function testExtractPropertyAnnotationsWithException(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();

        /** @var PropertyExtractor&MockObject $propertyExtractor */
        $propertyExtractor = $this
            ->getMockBuilder(PropertyExtractor::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
        $properties = $propertyExtractor->getProperties($someClass);

        /** @var AnnotationReader&MockObject $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPropertyAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getPropertyAnnotations')
            ->with($this->equalTo($properties[0]))
            ->willThrowException(new \InvalidArgumentException('message'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass (property32): message');

        $propertyExtractor = new AnnotationExtractor();
        $propertyExtractor->setAnnotationReader($mockAnnotationReader);
        $propertyExtractor->getAnnotationsForProperty($annotation::class, $properties[0]);
    }

    public function testExtractPropertyAnnotationsWithAttributeError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/BrokenAttributeProperty \(brokenProperty\):/');

        $brokenObject = new BrokenAttributeProperty();
        $propertyExtractor = new PropertyExtractor();
        $properties = $propertyExtractor->getProperties($brokenObject);

        $annotationExtractor = new AnnotationExtractor();
        // Use the BrokenAnnotation class that will actually cause a TypeError
        $annotationExtractor->getAnnotationsForProperty(BrokenAnnotation::class, $properties[0]);
    }

    public function testExtractMethodAnnotationsWithAttributeError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/BrokenAttributeMethod \(getBrokenMethod\):/');

        $brokenObject = new BrokenAttributeMethod();
        $methodExtractor = new MethodExtractor();
        $methods = $methodExtractor->getMethods($brokenObject);

        $annotationExtractor = new AnnotationExtractor();
        $annotationExtractor->getAnnotationsForMethod(BrokenMethodAnnotation::class, $methods[0]);
    }

    public function testExtractClassAnnotationsWithAttributeError(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/BrokenAttributeClass:/');

        $brokenObject = new BrokenAttributeClass();

        $annotationExtractor = new AnnotationExtractor();
        $annotationExtractor->getAnnotationsForClass(BrokenClassAnnotation::class, $brokenObject);
    }
}
