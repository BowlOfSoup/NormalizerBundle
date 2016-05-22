<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use BowlOfSoup\NormalizerBundle\Service\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use PHPUnit_Framework_TestCase;

class PropertyExtractorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Extracting property annotations.
     */
    public function testExtractPropertyAnnotations()
    {
        $annotation = new Normalize(array());
        $someClass = new SomeClass();
        $properties = $this->getStubClassExtractor()->getProperties($someClass);

        $annotationResult = array($annotation);

        $mockAnnotationReader = $this
            ->getMockBuilder('Doctrine\Common\Annotations\AnnotationReader')
            ->disableOriginalConstructor()
            ->setMethods(array('getPropertyAnnotations'))
            ->getMock();
        $mockAnnotationReader
            ->expects($this->once())
            ->method('getPropertyAnnotations')
            ->with($this->equalTo($properties[0]))
            ->will($this->returnValue($annotationResult));

        $propertyExtractor = new PropertyExtractor($mockAnnotationReader);
        $result = $propertyExtractor->extractPropertyAnnotations($properties[0], get_class($annotation));

        $this->assertArraySubset(array($annotation), $result);
    }

    /**
     * @testdox Get a value for a property.
     */
    public function testGetPropertyValue()
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
    public function testGetPropertyValueForceGetMethod()
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
     * @testdox Get a value for a property, force get method, no method available.
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to get property value. No get() method found for property property53
     *
     */
    public function testGetPropertyValueForceGetMethodNoMethodAvailable()
    {
        $someClass = new SomeClass();
        $properties = $this->getStubClassExtractor()->getProperties($someClass);
        foreach ($properties as $property) {
            if ('property53' === $property->getName()) {
                $this->getStubPropertyExtractor()->getPropertyValue(
                    $someClass,
                    $property,
                    PropertyExtractor::FORCE_PROPERTY_GET_METHOD
                );
            }
        }
    }

    /**
     * @testdox Get a value for a property by specifying method.
     */
    public function testGetPropertyValueByMethod()
    {
        $someClass = new SomeClass();
        $result = $this->getStubPropertyExtractor()->getPropertyValueByMethod($someClass, 'getProperty32');

        $this->assertSame(123, $result);
    }

    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetPropertyValueByMethodNoMethodAvailable()
    {
        $someClass = new SomeClass();
        $result = $this->getStubPropertyExtractor()->getPropertyValueByMethod($someClass, 'getProperty53');

        $this->assertSame(null, $result);
    }

    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetId()
    {
        $someClass = new SomeClass();
        $result = $this->getStubPropertyExtractor()->getId($someClass);

        $this->assertSame(777, $result);
    }


    /**
     * @return \BowlOfSoup\NormalizerBundle\Service\ClassExtractor
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
     * @return \BowlOfSoup\NormalizerBundle\Service\PropertyExtractor
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