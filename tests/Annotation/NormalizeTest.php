<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Annotation;

use BowlOfSoup\NormalizerBundle\Annotation\Normalize;
use PHPUnit\Framework\TestCase;

class NormalizeTest extends TestCase
{
    /**
     * @testdox Test annotation with valid property sets.
     */
    public function testNormalizeNoValidations(): void
    {
        $properties = $this->getValidSetOfProperties();
        $normalize = new Normalize($properties);

        $this->assertSame($properties['name'], $normalize->getName());
        $this->assertSame($properties['group'], $normalize->getGroup());
        $this->assertSame($properties['type'], $normalize->getType());
        $this->assertSame($properties['format'], $normalize->getFormat());
        $this->assertSame($properties['callback'], $normalize->getCallback());
        $this->assertSame($properties['skipEmpty'], $normalize->getSkipEmpty());
        $this->assertSame($properties['maxDepth'], $normalize->getMaxDepth());
    }

    /**
     * @testdox Test annotation with valid property sets, default value for format.
     */
    public function testNormalizeNoValidationsDefaultValueForFormat(): void
    {
        $properties = $this->getValidSetOfProperties();
        unset($properties['format']);
        $normalize = new Normalize($properties);

        $this->assertSame('Y-m-d', $normalize->getFormat());
    }

    /**
     * @testdox Test annotation, validation on empty property.
     */
    public function testNormalizeValidationEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter "name" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize" cannot be empty.');

        $properties = $this->getValidSetOfProperties();
        $properties['name'] = '';
        new Normalize($properties);
    }

    /**
     * @testdox Test annotation, validation on type property.
     */
    public function testNormalizeValidationType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type "dummy" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize" is not supported.');

        $properties = $this->getValidSetOfProperties();
        $properties['type'] = 'dummy';
        new Normalize($properties);
    }

    /**
     * @testdox Test annotation, validation if property input type is valid
     */
    public function testNormalizeValidationPropertyType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Wrong datatype used for property "group" for annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize"');

        $properties = $this->getValidSetOfProperties();
        $properties['group'] = 'dummy';
        new Normalize($properties);
    }

    /**
     * @testdox Test annotation with unknown property in array-based initialization
     */
    public function testNormalizeWithUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property "unknownProperty" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Normalize" is unknown.');

        $properties = $this->getValidSetOfProperties();
        $properties['unknownProperty'] = 'value';
        new Normalize($properties);
    }

    /**
     * @testdox Test annotation with attribute-style named parameters with explicit nulls
     */
    public function testNormalizeWithAttributeStyleNullParameters(): void
    {
        // This tests the attribute-style path with null parameters
        $normalize = new Normalize(
            name: null,
            group: ['api'],
            type: null,
            format: null,
            callback: null,
            normalizeCallbackResult: null,
            skipEmpty: null,
            maxDepth: null
        );

        $this->assertNull($normalize->getName());
        $this->assertSame(['api'], $normalize->getGroup());
    }

    /**
     * @testdox Test annotation with attribute-style string group parameter
     */
    public function testNormalizeWithAttributeStyleStringGroup(): void
    {
        // This tests the elseif branch where group is a string
        $normalize = new Normalize(
            name: 'test',
            group: 'api',  // String instead of array
            type: null,
            format: null,
            callback: null,
            normalizeCallbackResult: null,
            skipEmpty: null,
            maxDepth: null
        );

        $this->assertSame('test', $normalize->getName());
        $this->assertSame(['api'], $normalize->getGroup());  // Should be converted to array
    }

    private function getValidSetOfProperties(): array
    {
        return [
            'name' => 'New Name',
            'group' => ['group1', 'group2'],
            'type' => 'collection',
            'format' => 'Y-m-d',
            'callback' => 'toArray',
            'skipEmpty' => true,
            'maxDepth' => 2,
        ];
    }
}
