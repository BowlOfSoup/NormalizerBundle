<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

abstract class AbstractClass
{
    private string $property1 = 'string';

    protected $property2 = [];

    protected function someParentMethod(): string
    {
        return 'hello';
    }

    private function moreSecrets(): object
    {
        return new \stdClass();
    }
}
