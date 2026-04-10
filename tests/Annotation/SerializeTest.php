<?php

declare(strict_types=1);

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

    /**
     * @testdox Test annotation with unknown property in array-based initialization
     */
    public function testSerializeWithUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property "unknownProperty" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Serialize" is unknown.');

        $properties = $this->getValidSetOfProperties();
        $properties['unknownProperty'] = 'value';
        new Serialize($properties);
    }

    /**
     * @testdox Test annotation with attribute-style named parameters with explicit nulls
     */
    public function testSerializeWithAttributeStyleNullParameters(): void
    {
        // This tests the attribute-style path with null parameters
        $serialize = new Serialize(
            wrapElement: null,
            group: ['api'],
            sortProperties: null
        );

        $this->assertNull($serialize->getWrapElement());
        $this->assertSame(['api'], $serialize->getGroup());
    }

    /**
     * @testdox Test annotation with attribute-style string group parameter
     */
    public function testSerializeWithAttributeStyleStringGroup(): void
    {
        // This tests the elseif branch where group is a string
        $serialize = new Serialize(
            wrapElement: 'data',
            group: 'api',  // String instead of array
            sortProperties: null
        );

        $this->assertSame('data', $serialize->getWrapElement());
        $this->assertSame(['api'], $serialize->getGroup());  // Should be converted to array
    }

    private function getValidSetOfProperties(): array
    {
        return [
            'wrapElement' => 'data',
            'group' => ['group1', 'group2'],
        ];
    }
}
