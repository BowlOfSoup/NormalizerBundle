<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Tests\ArraySubset;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class AnnotationExtractorTest extends TestCase
{
    /** @var string */
    private const ANNOTATION_NORMALIZE = Normalize::class;

    /**
     * @testdox Extracting class annotations.
     *
     * @throws \ReflectionException
     */
    public function testExtractClassAnnotation(): void
    {
        $annotation = new Normalize([]);
        $annotationResult = [$annotation];

        $someClass = new SomeClass();
        $reflectedClass = new \ReflectionClass($someClass);

        /** @var \Doctrine\Common\Annotations\AnnotationReader $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
            ->disableOriginalConstructor()
            ->setMethods(['getClassAnnotations'])
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

    /**
     * @testdox Extracting class annotations, but no class (object) given.
     */
    public function testExtractClassAnnotationNoClassGiven(): void
    {
        /** @var \Doctrine\Common\Annotations\AnnotationReader $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
            ->disableOriginalConstructor()
            ->getMock();

        $classExtractor = new AnnotationExtractor();
        $classExtractor->setAnnotationReader($mockAnnotationReader);
        $this->assertIsArray($classExtractor->getAnnotationsForClass(static::ANNOTATION_NORMALIZE, []));
    }

    /**
     * @testdox Extracting method annotations.
     */
    public function testExtractMethodAnnotations(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor|\PHPUnit\Framework\MockObject\Stub\Stub $methodExtractor */
        $methodExtractor = $this
            ->getMockBuilder(MethodExtractor::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $methods = $methodExtractor->getMethods($someClass);

        $annotationResult = [$annotation];

        /** @var \Doctrine\Common\Annotations\AnnotationReader $mockAnnotationReader */
        $mockAnnotationReader = $this
            ->getMockBuilder(AnnotationReader::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethodAnnotations'])
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->with($this->equalTo($methods[0]))
            ->willReturn($annotationResult);

        $methodExtractor = new AnnotationExtractor();
        $methodExtractor->setAnnotationReader($mockAnnotationReader);
        $result = $methodExtractor->getAnnotationsForMethod(get_class($annotation), $methods[0], get_class($someClass));

        ArraySubset::assert([$annotation], $result);
    }

    /**
     * @testdox Extracting property annotations.
     */
    public function testExtractPropertyAnnotations(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor|\PHPUnit\Framework\MockObject\Stub\Stub $propertyExtractor */
        $propertyExtractor = $this
            ->getMockBuilder(PropertyExtractor::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $properties = $propertyExtractor->getProperties($someClass);

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

        $propertyExtractor = new AnnotationExtractor();
        $propertyExtractor->setAnnotationReader($mockAnnotationReader);
        $result = $propertyExtractor->getAnnotationsForProperty(get_class($annotation), $properties[0], get_class($someClass));

        ArraySubset::assert([$annotation], $result);
    }
}
