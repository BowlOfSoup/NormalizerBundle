<?php

namespace BowlOfSoup\NormalizerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BowlOfSoupNormalizerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
