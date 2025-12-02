<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class Group
{
    /**
     * @Bos\Normalize(group={"maxDepthTestDepth1", "default"})
     */
    private int $id;

    /**
     * @Bos\Normalize(group={"maxDepthTestDepth1", "default"})
     */
    private string $name;

    /**
     * @Bos\Normalize(group={"default"})
     *
     * @var Person[]|null
     */
    private ?array $persons = null;

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

    /**
     * @return Person[]
     */
    public function getPersons(): array
    {
        return $this->persons;
    }

    /**
     * @param Person[] $persons
     *
     * @return $this
     */
    public function setPersons(array $persons): self
    {
        $this->persons = $persons;

        return $this;
    }
}
