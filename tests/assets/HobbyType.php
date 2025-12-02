<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class HobbyType
{
    /**
     * @Bos\Normalize(group={"default", "duplicateObjectId"})
     */
    private int $id;

    /**
     * @Bos\Normalize(group={"default", "duplicateObjectId"})
     */
    private string $name;

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

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
