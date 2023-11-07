<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class Group
{
    /**
     * @Bos\Normalize(group={"maxDepthTestDepth1", "default"})
     *
     * @var int
     */
    private $id;

    /**
     * @Bos\Normalize(group={"maxDepthTestDepth1", "default"})
     *
     * @var string
     */
    private $name;

    /**
     * @Bos\Normalize(group={"default"})
     *
     * @var \BowlOfSoup\NormalizerBundle\Tests\assets\Person[]|array|null
     */
    private $persons = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \BowlOfSoup\NormalizerBundle\Tests\assets\Person[]
     */
    public function getPersons(): array
    {
        return $this->persons;
    }

    /**
     * @param \BowlOfSoup\NormalizerBundle\Tests\assets\Person[] $persons
     *
     * @return $this
     */
    public function setPersons(array $persons): self
    {
        $this->persons = $persons;

        return $this;
    }
}
