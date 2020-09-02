<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use Doctrine\Common\Persistence\Proxy;

class ProxySocial extends Social implements Proxy
{
    /**
     * {@inheritdoc}
     */
    public function __load()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __isInitialized()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFacebook()
    {
        return parent::getFacebook();
    }
}
