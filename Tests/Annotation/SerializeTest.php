<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Annotation;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use PHPUnit_Framework_TestCase;

class SerializeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Test annotation with valid property sets.
     */
    public function testSerializeNoValidations()
    {
        $properties = $this->getValidSetOfProperties();
        $serialize = new Serialize($properties);

        $this->assertSame($properties['group'], $serialize->getGroup());
        $this->assertSame($properties['type'], $serialize->getType());
        $this->assertSame($properties['wrapElement'], $serialize->getWrapElement());
    }

    /**
     * @testdox Test annotation, validation on type property.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Type "dummy" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Serialize" is not supported.
     */
    public function testSerializeValidationType()
    {
        $properties = $this->getValidSetOfProperties();
        $properties['type'] = 'dummy';
        new Serialize($properties);
    }

    /**
     * @testdox Test annotation, validation if property input type is valid
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Wrong datatype used for property "group" for annotation "BowlOfSoup\NormalizerBundle\Annotation\Serialize"
     */
    public function testSerializeValidationPropertyType()
    {
        $properties = $this->getValidSetOfProperties();
        $properties['group'] = 'dummy';
        new Serialize($properties);
    }

    /**
     * @return array
     */
    private function getValidSetOfProperties()
    {
        return array(
            'wrapElement' => 'data',
            'group' => array('group1', 'group2'),
            'type' => 'xml',
        );
    }
}
