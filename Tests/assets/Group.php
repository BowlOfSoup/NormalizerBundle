<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class Group
{
    /**
     * @Bos\Normalize(group={"maxDepthTestDepth1"})
     *
     * @var int
     */
    private $id;

    /**
     * @Bos\Normalize(group={"maxDepthTestDepth1"})
     *
     * @var string
     */
    private $name;

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
}
