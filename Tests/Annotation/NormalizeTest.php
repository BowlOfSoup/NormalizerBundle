<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Annotation;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use PHPUnit_Framework_TestCase;

class NormalizeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Test annotation with valid property sets.
     */
    public function testNormalizeNoValidations()
    {
        $properties = $this->getValidSetOfProperties();
        $normalize = new Normalize($properties);

        $this->assertSame($properties['name'], $normalize->getName());
        $this->assertSame($properties['group'], $normalize->getGroup());
        $this->assertSame($properties['type'], $normalize->getType());
        $this->assertSame($properties['format'], $normalize->getFormat());
        $this->assertSame($properties['callback'], $normalize->getCallback());
        $this->assertSame($properties['skipEmpty'], $normalize->getSkipEmpty());
    }

    /**
     * @testdox Test annotation with valid property sets, default value for format.
     */
    public function testNormalizeNoValidationsDefaultValueForFormat()
    {
        $properties = $this->getValidSetOfProperties();
        unset($properties['format']);
        $normalize = new Normalize($properties);

        $this->assertSame('Y-m-d', $normalize->getFormat());
    }

    /**
     * @testdox Test annotation, validation on empty property.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Parameter "name" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize" cannot be empty.
     */
    public function testNormalizeValidationEmpty()
    {
        $properties = $this->getValidSetOfProperties();
        $properties['name'] = '';
        new Normalize($properties);
    }

    /**
     * @testdox Test annotation, validation on type property.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Type "dummy" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize" is not supported.
     */
    public function testNormalizeValidationType()
    {
        $properties = $this->getValidSetOfProperties();
        $properties['type'] = 'dummy';
        new Normalize($properties);
    }

    /**
     * @testdox Test annotation, validation if property input type is valid
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong datatype used for property "group" for annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize"
     */
    public function testNormalizeValidationPropertyType()
    {
        $properties = $this->getValidSetOfProperties();
        $properties['group'] = 'dummy';
        new Normalize($properties);
    }

    /**
     * @return array
     */
    private function getValidSetOfProperties()
    {
        return array(
            'name' => 'New Name',
            'group' => array('group1', 'group2'),
            'type' => 'collection',
            'format' => 'Y-m-d',
            'callback' => 'toArray',
            'skipEmpty' => true,
        );
    }
}
