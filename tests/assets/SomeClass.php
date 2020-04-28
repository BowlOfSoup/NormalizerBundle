<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

class SomeClass extends AbstractClass
{
    private $property32 = 123;

    public $property53 = 'string';

    private $property76 = 'another value';

    public function getProperty32()
    {
        return $this->property32;
    }

    public function getId()
    {
        return 777;
    }
}
