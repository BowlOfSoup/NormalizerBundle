<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Common\Persistence\Proxy;

class ProxyObject implements Proxy
{
    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $value = 'Hello';

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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}