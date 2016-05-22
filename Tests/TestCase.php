<?php

namespace BowlOfSoup\NormalizerBundle\Tests;

use PHPUnit_Framework_TestCase;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /** string, used as keys in array for mocking */
    const INVOCATION = 'invocation';

    /** string, used as keys in array for mocking */
    const RETURN_VALUE = 'returnValue';

    /**
     * Invoke a class method, even though it has access level "protected" or "private".
     *
     * @param mixed $object
     * @param string $method
     * @param array $arguments
     *
     * @return mixed
     */
    protected function invokeMethod($object, $method, array $arguments = array())
    {
        $reflectionClass = new ReflectionClass($object);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($reflectionMethod->isStatic() ? null : $object, $arguments);
    }

    /**
     * Set a class property value, even though it has access level "protected".
     *
     * @param mixed $object
     * @param string $property
     * @param mixed $value
     */
    protected function setProperty($object, $property, $value)
    {
        $reflectionClass = new ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * Get a class property value, even though it has access level "protected".
     *
     * @param mixed $object
     * @param string $property
     *
     * @return mixed
     */
    protected function getProperty($object, $property)
    {
        $reflectionClass = new ReflectionClass($object);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Creates a dummy.
     *
     * When a particular type of object is required as an argument, but it is not used in any significant way,
     * use a dummy.
     *
     * @param string $className
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function dummy($className)
    {
        return $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
    }

    /**
     * When a test double is supposed to return some fixed values, you need a stub.
     *
     * The characteristics of a stub are:
     * It does not matter which arguments are provided when one of its methods is called.
     * It does not matter how many times a method is called.
     *
     * @param string $className
     * @param array  $methodAndReturnValue
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function stub($className, array $methodAndReturnValue = array(), array $constructorArgs = array())
    {
        $methodNames = null;
        if (!empty($methodAndReturnValue)) {
            $methodNames = array_keys($methodAndReturnValue);
        }

        $mockBuilder = $this
            ->getMockBuilder($className)
            ->setMethods($methodNames);

        if (empty($constructorArgs)) {
            $mockBuilder = $mockBuilder->disableOriginalConstructor();
        } else {
            $mockBuilder = $mockBuilder->setConstructorArgs($constructorArgs);
        }

        $stub = $mockBuilder->getMock();

        foreach ($methodAndReturnValue as $method => $returnValue) {
            $stub
                ->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $stub;
    }

    /**
     * Creates a mock.
     *
     * If you want to keep track of which method calls are being made and how many times they are being called in
     * combination with what they return, then you need a mock.
     *
     * $methodInputAndOutputValue can contain:
     * array(
     *     'methodA' => array(
     *         array('invocation' => $this->at(0), 'returnValue' => $returnValueA),
     *         array('invocation' => $this->at(1), 'returnValue' => $returnValueB),
     *     ),
     *     'methodB' => array(
     *         array('invocation' => atLeastOnce(), 'returnValue' => $returnValueX),
     *     ),
     * )
     *
     * $constructorArgs can contain an empty array, the constructor will be disabled.
     *
     * @param $className
     * @param array $methodInvocationAndReturnValue
     * @param array $constructorArgs
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function mock($className, array $methodInvocationAndReturnValue, array $constructorArgs = array())
    {
        $mockBuilder = $this->getMockBuilder($className);

        if (empty($constructorArgs)) {
            $mockBuilder = $mockBuilder->disableOriginalConstructor();
        } else {
            $mockBuilder = $mockBuilder->setConstructorArgs($constructorArgs);
        }

        $mock = $mockBuilder->setMethods(array_keys($methodInvocationAndReturnValue))
            ->getMock();

        foreach ($methodInvocationAndReturnValue as $method => $testcases) {
            foreach ($testcases as $testcase) {
                $mock
                    ->expects($testcase[static::INVOCATION])
                    ->method($method)
                    ->will($this->returnValue($testcase[static::RETURN_VALUE]));
            }
        }

        return $mock;
    }
}