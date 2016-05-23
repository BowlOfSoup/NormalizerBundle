<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class SomeClass extends AbstractClass
{
    private $property32 = 123;

    public $property53 = 'string';

    public function getProperty32()
    {
        return $this->property32;
    }

    public function getId()
    {
        return 777;
    }
}
