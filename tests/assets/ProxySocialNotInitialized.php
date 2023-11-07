<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use Doctrine\Persistence\Proxy;

class ProxySocialNotInitialized extends Social implements Proxy
{
    public function __load()
    {
    }

    public function __isInitialized(): bool
    {
        return false;
    }
}
