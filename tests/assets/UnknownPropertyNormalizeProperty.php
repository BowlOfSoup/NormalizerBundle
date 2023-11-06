<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class UnknownPropertyNormalizeProperty
{
    /**
     * @Bos\Normalize(group={"default"}, asdsad="asdsad")
     */
    private ?string $name = null;

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
