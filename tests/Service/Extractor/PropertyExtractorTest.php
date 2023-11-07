<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use BowlOfSoup\NormalizerBundle\Tests\assets\ProxyObject;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use PHPUnit\Framework\TestCase;

class PropertyExtractorTest extends TestCase
{
    /** @var (\BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor&\PHPUnit\Framework\MockObject\MockObject)|\PHPUnit\Framework\MockObject\MockObject */
    private $propertyExtractor;

    protected function setUp(): void
    {
        $this->propertyExtractor = $this
            ->getMockBuilder(PropertyExtractor::class)
            ->disableOriginalConstructor()
            ->addMethods([])
            ->getMock();
    }

    /**
     * @testdox Try to get methods from a non-object.
     */
    public function testGetMethodsForNothing(): void
    {
        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $propertyExtractor */
        $propertyExtractor = $this->propertyExtractor;
        $result = $propertyExtractor->getProperties('foo');

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
            ->addMethods([])
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
     * @testdox Get a value for a property.
     */
    public function testGetPropertyValue(): void
    {
        $someClass = new SomeClass();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $propertyExtractor */
        $propertyExtractor = $this->propertyExtractor;
        $properties = $propertyExtractor->getProperties($someClass);
        foreach ($properties as $property) {
            if ('property53' === $property->getName()) {
                $result = $propertyExtractor->getPropertyValue($someClass, $property);

                $this->assertSame('string', $result);
            }
        }
    }

    /**
     * @testdox Get a value for a property, force get method, no method available, force get, is Doctrine Proxy.
     */
    public function testGetPropertyValueForceGetMethodNoMethodAvailableDoctrineProxy(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Unable to initiate Doctrine proxy, not get() method found for property proxyProperty');

        $proxyObject = new ProxyObject();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $propertyExtractor */
        $propertyExtractor = $this->propertyExtractor;
        $properties = $propertyExtractor->getProperties($proxyObject);
        foreach ($properties as $property) {
            if ('proxyProperty' === $property->getName()) {
                $propertyExtractor->getPropertyValue(
                    $proxyObject,
                    $property
                );
            }
        }
    }

    /**
     * @testdox Get a value for a property, Doctrine Proxy, force get method, assert ID = integer.
     */
    public function testGetPropertyDoctrineProxyForceGetMethodAssertIdInteger(): void
    {
        $result = null;

        $proxyObject = new ProxyObject();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $propertyExtractor */
        $propertyExtractor = $this->propertyExtractor;
        $properties = $propertyExtractor->getProperties($proxyObject);
        foreach ($properties as $property) {
            if ('id' === $property->getName()) {
                $result = $propertyExtractor->getPropertyValue(
                    $proxyObject,
                    $property
                );
            }
        }

        $this->assertSame(123, $result);
    }

    public function testGetPropertyForceGetMethodBecauseOfException(): void
    {
        $person = new Person();
        $person->setSurName('BowlOfSoup');

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $propertyExtractor */
        $propertyExtractor = $this->propertyExtractor;

        $reflectionPropertyMock = $this
            ->getMockBuilder(\ReflectionProperty::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getValue', 'getName'])
            ->getMock();
        $reflectionPropertyMock
            ->expects($this->once())
            ->method('getName')
            ->withAnyParameters()
            ->willReturn('SurName');
        $reflectionPropertyMock
            ->expects($this->once())
            ->method('getValue')
            ->withAnyParameters()
            ->willThrowException(new \ReflectionException('foo'));

        $result = $propertyExtractor->getPropertyValue(
            $person,
            $reflectionPropertyMock
        );

        $this->assertSame('BowlOfSoup', $result);
    }

    /**
     * @testdox Get a value for a property by specifying method.
     */
    public function testGetPropertyValueByMethod(): void
    {
        $someClass = new SomeClass();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $propertyExtractor */
        $propertyExtractor = $this->propertyExtractor;
        $result = $propertyExtractor->getPropertyValueByMethod($someClass, 'getProperty32');

        $this->assertSame(123, $result);
    }

    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetPropertyValueByMethodNoMethodAvailable(): void
    {
        $someClass = new SomeClass();

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor $propertyExtractor */
        $propertyExtractor = $this->propertyExtractor;
        $result = $propertyExtractor->getPropertyValueByMethod($someClass, 'getProperty53');

        $this->assertNull($result);
    }
}
