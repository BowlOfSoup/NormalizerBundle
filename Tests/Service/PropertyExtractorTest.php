<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Service\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Tests\ArraySubset;
use BowlOfSoup\NormalizerBundle\Tests\assets\ProxyObject;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\TestCase;

class PropertyExtractorTest extends TestCase
{
    /**
     * @testdox Extracting property annotations.
     */
    public function testExtractPropertyAnnotations(): void
    {
        $annotation = new Normalize([]);
        $someClass = new SomeClass();
        $properties = $this->getStubClassExtractor()->getProperties($someClass);

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
     * @testdox Get a value for a property.
     */
    public function testGetPropertyValue(): void
    {
        $someClass = new SomeClass();
        $properties = $this->getStubClassExtractor()->getProperties($someClass);
        foreach ($properties as $property) {
            if ('property53' === $property->getName()) {
                $result = $this->getStubPropertyExtractor()->getPropertyValue($someClass, $property);

                $this->assertSame('string', $result);
            }
        }
    }

    /**
     * @testdox Get a value for a property, force get method
     */
    public function testGetPropertyValueForceGetMethod(): void
    {
        $someClass = new SomeClass();
        $properties = $this->getStubClassExtractor()->getProperties($someClass);
        foreach ($properties as $property) {
            if ('property32' === $property->getName()) {
                $result = $this->getStubPropertyExtractor()->getPropertyValue(
                    $someClass,
                    $property,
                    PropertyExtractor::FORCE_PROPERTY_GET_METHOD
                );

                $this->assertSame(123, $result);
            }
        }
    }

    /**
     * @testdox Get a value for a property, force get method, no method available, force get from public/protected.
     */
    public function testGetPropertyValueForceGetMethodNoMethodAvailable(): void
    {
        $someClass = new SomeClass();
        $properties = $this->getStubClassExtractor()->getProperties($someClass);
        foreach ($properties as $property) {
            if ('property53' === $property->getName()) {
                $result = $this->getStubPropertyExtractor()->getPropertyValue(
                    $someClass,
                    $property,
                    PropertyExtractor::FORCE_PROPERTY_GET_METHOD
                );

                $this->assertSame('string', $result);
            }
        }
    }

    /**
     * @testdox Get a value for a property, force get method, no method available, force get, but not public/protected.
     */
    public function testGetPropertyValueForceGetMethodNoMethodAvailableNoAccess(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Unable to get property value. No get() method found for property property76');

        $someClass = new SomeClass();
        $properties = $this->getStubClassExtractor()->getProperties($someClass);
        foreach ($properties as $property) {
            if ('property76' === $property->getName()) {
                $this->getStubPropertyExtractor()->getPropertyValue(
                    $someClass,
                    $property,
                    PropertyExtractor::FORCE_PROPERTY_GET_METHOD
                );
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
        $properties = $this->getStubClassExtractor()->getProperties($proxyObject);
        foreach ($properties as $property) {
            if ('proxyProperty' === $property->getName()) {
                $this->getStubPropertyExtractor()->getPropertyValue(
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
        $properties = $this->getStubClassExtractor()->getProperties($proxyObject);
        foreach ($properties as $property) {
            if ('id' === $property->getName()) {
                $result = $this->getStubPropertyExtractor()->getPropertyValue(
                    $proxyObject,
                    $property
                );
            }
        }

        $this->assertSame(123, $result);
    }

    /**
     * @testdox Get a value for a property by specifying method.
     */
    public function testGetPropertyValueByMethod(): void
    {
        $someClass = new SomeClass();
        $result = $this->getStubPropertyExtractor()->getPropertyValueByMethod($someClass, 'getProperty32');

        $this->assertSame(123, $result);
    }

    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetPropertyValueByMethodNoMethodAvailable(): void
    {
        $someClass = new SomeClass();
        $result = $this->getStubPropertyExtractor()->getPropertyValueByMethod($someClass, 'getProperty53');

        $this->assertNull($result);
    }

    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetId(): void
    {
        $someClass = new SomeClass();
        $result = $this->getStubPropertyExtractor()->getId($someClass);

        $this->assertSame(777, $result);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\BowlOfSoup\NormalizerBundle\Service\ClassExtractor
     */
    private function getStubClassExtractor()
    {
        return $this
            ->getMockBuilder('BowlOfSoup\NormalizerBundle\Service\ClassExtractor')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\BowlOfSoup\NormalizerBundle\Service\PropertyExtractor
     */
    private function getStubPropertyExtractor()
    {
        return $this
            ->getMockBuilder('BowlOfSoup\NormalizerBundle\Service\PropertyExtractor')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }
}
