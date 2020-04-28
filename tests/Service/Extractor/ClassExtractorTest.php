<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use PHPUnit\Framework\TestCase;

class ClassExtractorTest extends TestCase
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

        $classExtractor = new ClassExtractor($mockAnnotationReader);
        $classExtractor->extractClassAnnotations($someClass, static::ANNOTATION_NORMALIZE);
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

        $classExtractor = new ClassExtractor($mockAnnotationReader);

        $this->assertIsArray($classExtractor->extractClassAnnotations([], static::ANNOTATION_NORMALIZE));
    }
}
