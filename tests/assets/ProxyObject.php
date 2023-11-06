<?php

declare(strict_types=1);

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
