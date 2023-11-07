<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use Doctrine\Persistence\Proxy;

class ProxySocial extends Social implements Proxy
{
    public function __load()
    {
    }

    public function __isInitialized()
    {
        return true;
    }

    public function getFacebook()
    {
        return parent::getFacebook();
    }
}
