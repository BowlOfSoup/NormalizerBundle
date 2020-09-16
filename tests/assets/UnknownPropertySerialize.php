<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

/**
 * @Bos\Serialize(wrappElement="data", group={"default"})
 */
class UnknownPropertySerialize
{
    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"}, asdsad="asdsad")
     */
    private $name;

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
