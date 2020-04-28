<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Common\Collections\Collection;

class Address
{
    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $street;

    /**
     * @var int
     *
     * @Bos\Normalize(group={"default"})
     */
    private $number;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     */
    private $postalCode;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"}, callback="getCityWithFormat")
     */
    private $city;

    /**
     * @var Collection
     *
     * @Bos\Normalize(group={"maxDepthTestDepth1"}, type="collection")
     */
    private $group;

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getCityWithFormat()
    {
        return 'The City Is: ' . $this->city;
    }

    /**
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection
     *
     * @Bos\Normalize(type="collection", name="group", group={"maxDepthTestDepth1OnMethod"})
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return $this
     */
    public function setGroup(Collection $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @Bos\Normalize(group={"default"})
     * @Bos\Normalize(group={"anotherGroup"})
     */
    public function getSpecialNotesForDelivery(): string
    {
        return 'some special string';
    }

    /**
     * @Bos\Normalize(group={"default"}, skipEmpty=true)
     */
    public function getRouteToFrontDoor(): string
    {
        return '';
    }
}
