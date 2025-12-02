<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class Hobbies
{
    /**
     * @Bos\Normalize(group={"default", "duplicateObjectId"})
     */
    private ?int $id = null;

    /**
     * @Bos\Normalize(group={"default", "duplicateObjectId"})
     */
    private string $description;

    /**
     * @Bos\Normalize(group={"default", "duplicateObjectId"}, type="object")
     */
    private HobbyType $hobbyType;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getHobbyType(): HobbyType
    {
        return $this->hobbyType;
    }

    /**
     * @return $this
     */
    public function setHobbyType(HobbyType $hobbyType): self
    {
        $this->hobbyType = $hobbyType;

        return $this;
    }
}
