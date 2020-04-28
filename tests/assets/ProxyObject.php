<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Common\Persistence\Proxy;

class ProxyObject implements Proxy
{
    /** @var string */
    private $id = '123';

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $value = 'Hello';

    /** @var string */
    private $proxyProperty = 'string';

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
