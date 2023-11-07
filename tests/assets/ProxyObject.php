<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Persistence\Proxy;

class ProxyObject implements Proxy
{
    /** @var string */
    private $id = '123';

    /**
     * @Bos\Normalize(group={"default"})
     *
     * @var string
     */
    private $value = 'Hello';

    /** @var string */
    private $proxyProperty = 'string';

    public function __load()
    {
    }

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
