<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Annotation;

use BowlOfSoup\NormalizerBundle\Annotation\Translate;
use PHPUnit\Framework\TestCase;

class TranslateTest extends TestCase
{
    /**
     * @testdox Test annotation with valid property sets.
     */
    public function testTranslateNoValidations(): void
    {
        $properties = $this->getValidSetOfProperties();
        $translate = new Translate($properties);

        $this->assertSame($properties['group'], $translate->getGroup());
        $this->assertSame($properties['domain'], $translate->getDomain());
        $this->assertSame($properties['locale'], $translate->getLocale());
    }

    /**
     * @testdox Test annotation, validation if property input type is valid
     */
    public function testTranslateValidationPropertyType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Wrong datatype used for property "group" for annotation "BowlOfSoup\NormalizerBundle\Annotation\Translate"');

        $properties = $this->getValidSetOfProperties();
        $properties['group'] = 'dummy';
        new Translate($properties);
    }

    /**
     * @testdox Test annotation with unknown property in array-based initialization
     */
    public function testTranslateWithUnknownProperty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Property "unknownProperty" of annotation "BowlOfSoup\NormalizerBundle\Annotation\Translate" is unknown.');

        $properties = $this->getValidSetOfProperties();
        $properties['unknownProperty'] = 'value';
        new Translate($properties);
    }

    /**
     * @testdox Test annotation with attribute-style named parameters with explicit nulls
     */
    public function testTranslateWithAttributeStyleNullParameters(): void
    {
        // This tests the attribute-style path with null parameters
        $translate = new Translate(
            domain: null,
            group: ['api'],
            locale: null
        );

        $this->assertNull($translate->getDomain());
        $this->assertSame(['api'], $translate->getGroup());
        $this->assertNull($translate->getLocale());
    }

    /**
     * @testdox Test annotation with attribute-style string group parameter
     */
    public function testTranslateWithAttributeStyleStringGroup(): void
    {
        // This tests the elseif branch where group is a string
        $translate = new Translate(
            domain: 'messages',
            group: 'api',  // String instead of array
            locale: 'en'
        );

        $this->assertSame('messages', $translate->getDomain());
        $this->assertSame(['api'], $translate->getGroup());  // Should be converted to array
        $this->assertSame('en', $translate->getLocale());
    }

    private function getValidSetOfProperties(): array
    {
        return [
            'domain' => 'messages',
            'group' => ['group1', 'group2'],
            'locale' => 'en',
        ];
    }
}
