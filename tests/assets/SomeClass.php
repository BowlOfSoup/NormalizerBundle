<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

class SomeClass extends AbstractClass
{
    /** @var int */
    private $property32 = 123;

    /** @var string */
    public $property53 = 'string';

    /** @var string */
    private $property76 = 'another value';

    public function getProperty32()
    {
        return $this->property32;
    }

    public function getId()
    {
        return 777;
    }

    public function someVeryDifficultMethod(int $input): string
    {
        return 'something';
    }

    protected function itProtec(): callable
    {
        return static function () {};
    }

    private function secret(): object
    {
        return new \stdClass();
    }

    public static function thisIsNotTestable(): int
    {
        return 123;
    }
}
