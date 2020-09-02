<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

/**
 * @Bos\Normalize(group={"default"}, skipEmpty=true)
 */
class Social
{
    /** @var int */
    private $id;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $facebook;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $twitter;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $instagram;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $snapchat;

    /**
     * Used to show cirular reference for the normalizer.
     *
     * @var Person
     *
     * @Bos\Normalize(group={"default"}, type="object")
     */
    private $person;

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
     * @return string
     *
     * @Bos\Normalize(name="facebook", group={"proxy-method"})
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param string $facebook
     *
     * @return $this
     */
    public function setFacebook($facebook)
    {
        $this->facebook = $facebook;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param string $twitter
     *
     * @return $this
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;

        return $this;
    }

    /**
     * @return string
     */
    public function getInstagram()
    {
        return $this->instagram;
    }

    /**
     * @param string $instagram
     *
     * @return $this
     */
    public function setInstagram($instagram)
    {
        $this->instagram = $instagram;

        return $this;
    }

    /**
     * @return string
     */
    public function getSnapchat()
    {
        return $this->snapchat;
    }

    /**
     * @param string $snapchat
     *
     * @return $this
     */
    public function setSnapchat($snapchat)
    {
        $this->snapchat = $snapchat;

        return $this;
    }

    /**
     * @return Person
     *
     * @Bos\Normalize(type="object", group={"circRefMethod"})
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }
}
