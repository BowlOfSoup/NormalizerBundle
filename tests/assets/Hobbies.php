<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class Hobbies
{
    /**
     * @Bos\Normalize(group={"default", "duplicateObjectId"})
     *
     * @var int|null
     */
    private $id = null;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default", "duplicateObjectId"})
     */
    private $description;

    /**
     * @var HobbyType
     *
     * @Bos\Normalize(group={"default", "duplicateObjectId"}, type="object")"}, type="object")
     */
    private $hobbyType;

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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return HobbyType
     */
    public function getHobbyType()
    {
        return $this->hobbyType;
    }

    /**
     * @param HobbyType $hobbyType
     *
     * @return $this
     */
    public function setHobbyType($hobbyType)
    {
        $this->hobbyType = $hobbyType;

        return $this;
    }
}
