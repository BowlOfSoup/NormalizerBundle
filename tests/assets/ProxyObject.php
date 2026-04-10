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

    public function __load(): void
    {
    }

    public function __isInitialized(): bool
    {
        return true;
    }

    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
