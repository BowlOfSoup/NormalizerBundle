<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Persistence\Proxy;

class ProxyObject implements Proxy
{
    private string $id = '123';

    /**
     * @Bos\Normalize(group={"default"})
     */
    private string $value = 'Hello';

    private string $proxyProperty = 'string';

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
        return true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
