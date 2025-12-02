<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use Doctrine\Persistence\Proxy;

class ProxySocial extends Social implements Proxy
{
    public function __load(): void
    {
    }

    public function __isInitialized(): bool
    {
        return true;
    }

    #[\Override]
    public function getFacebook(): string
    {
        return parent::getFacebook();
    }
}
