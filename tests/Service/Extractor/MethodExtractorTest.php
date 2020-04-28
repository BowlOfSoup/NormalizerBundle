<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Tests\ArraySubset;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class MethodExtractorTest extends TestCase
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor|\PHPUnit\Framework\MockObject\MockObject */
    private $methodExtractor;

    protected function setUp(): void
    {
        $this->methodExtractor = $this
            ->getMockBuilder(MethodExtractor::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    /**
     * @testdox Get all methods of a class.
     */
    public function testGetMethods()
    {
        $someClass = new SomeClass();
        $methods = $this->methodExtractor->getMethods($someClass);
        $this->assertCount(8, $methods);

        $method = $methods[0];
        $this->assertSame('getProperty32', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertSame(123, $method->invoke($someClass));

        $method = $methods[1];
        $this->assertSame('getId', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertSame(777, $method->invoke($someClass));

        $method = $methods[2];
        $this->assertSame('someVeryDifficultMethod', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertSame('something', $method->invoke($someClass, 123));

        $method = $methods[3];
        $this->assertSame('itProtec', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $method->setAccessible(true);
        $this->assertIsCallable($method->invoke($someClass));

        $method = $methods[4];
        $this->assertSame('secret', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $method->setAccessible(true);
        $this->assertIsObject($method->invoke($someClass));

        $method = $methods[5];
        $this->assertSame('thisIsNotTestable', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $this->assertSame(123, $method->invoke($someClass));

        $method = $methods[6];
        $this->assertSame('someParentMethod', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $method->setAccessible(true);
        $this->assertSame('hello', $method->invoke($someClass));

        $method = $methods[7];
        $this->assertSame('moreSecrets', $method->getName());
        $this->assertInstanceOf(\ReflectionMethod::class, $method);
        $method->setAccessible(true);
        $this->assertIsObject($method->invoke($someClass));
    }

    /**
     * @testdox Extracting method annotations.
     */
    public function testExtractPropertyAnnotations(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();
        $methods = $this->methodExtractor->getMethods($someClass);

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

        $methodExtractor = new MethodExtractor($mockAnnotationReader);
        $result = $methodExtractor->extractMethodAnnotations($methods[0], get_class($annotation));

        ArraySubset::assert([$annotation], $result);
    }

    /**
     * @testdox Get a value for a property.
     */
    public function testGetPropertyValue(): void
    {
        $someClass = new SomeClass();
        $methods = $this->methodExtractor->getMethods($someClass);
        foreach ($methods as $method) {
            if ('getProperty32' === $method->getName()) {
                $result = $this->methodExtractor->getValueByMethod($someClass, $method->getName());

                $this->assertSame(123, $result);
            }
        }
    }
}
