<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use PHPUnit\Framework\TestCase;

class MethodExtractorTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|(\BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor&\PHPUnit\Framework\MockObject\MockObject) */
    private $methodExtractor;

    protected function setUp(): void
    {
        $this->methodExtractor = $this
            ->getMockBuilder(MethodExtractor::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
    }

    /**
     * @testdox Try to get methods from a non-object.
     */
    public function testGetMethodsForNothing(): void
    {
        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor $methodExtractor */
        $methodExtractor = $this->methodExtractor;
        $result = $methodExtractor->getMethods('foo');

        $this->assertEmpty($result);
        $this->assertIsArray($result);
    }

    /**
     * @testdox Get all methods of a class.
     */
    public function testGetMethods()
    {
        $someClass = new SomeClass();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor $methodExtractor */
        $methodExtractor = $this->methodExtractor;
        $methods = $methodExtractor->getMethods($someClass);
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
}
