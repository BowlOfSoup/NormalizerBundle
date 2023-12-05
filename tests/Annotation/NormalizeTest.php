<?php

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
        $this->assertSame($properties['allowEmptyArray'], $normalize->getAllowEmptyArray());
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
            'allowEmptyArray' => false,
        ];
    }
}
