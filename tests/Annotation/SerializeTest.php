<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Annotation;

use BowlOfSoup\NormalizerBundle\Annotation\Serialize;
use PHPUnit\Framework\TestCase;

class SerializeTest extends TestCase
{
    /**
     * @testdox Test annotation with valid property sets.
     */
    public function testSerializeNoValidations(): void
    {
        $properties = $this->getValidSetOfProperties();
        $serialize = new Serialize($properties);

        $this->assertSame($properties['group'], $serialize->getGroup());
        $this->assertSame($properties['wrapElement'], $serialize->getWrapElement());
    }

    /**
     * @testdox Test annotation, validation if property input type is valid
     */
    public function testSerializeValidationPropertyType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Wrong datatype used for property "group" for annotation "BowlOfSoup\NormalizerBundle\Annotation\Serialize"');

        $properties = $this->getValidSetOfProperties();
        $properties['group'] = 'dummy';
        new Serialize($properties);
    }

    private function getValidSetOfProperties(): array
    {
        return [
            'wrapElement' => 'data',
            'group' => ['group1', 'group2'],
        ];
    }
}
