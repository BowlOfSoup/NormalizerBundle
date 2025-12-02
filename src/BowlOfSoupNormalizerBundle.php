<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BowlOfSoupNormalizerBundle extends Bundle
{
    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
