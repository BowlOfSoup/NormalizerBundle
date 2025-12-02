<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

abstract class AbstractClass
{
    protected array $property2 = [];

    private string $property1 = 'string';

    protected function someParentMethod(): string
    {
        return 'hello';
    }

    private function moreSecrets(): object
    {
        return new \stdClass();
    }
}
