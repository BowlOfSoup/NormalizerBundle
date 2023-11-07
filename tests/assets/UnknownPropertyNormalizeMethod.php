<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class UnknownPropertyNormalizeMethod
{
    /** @var string|null */
    private $name = null;

    /**
     * @Bos\Normalize(group={"default"}, asdsad="asdsad")
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
