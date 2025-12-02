<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Common\Collections\Collection;

class Address
{
    /**
     * @Bos\Normalize(group={"default"})
     */
    private ?string $street = null;

    /**
     * @Bos\Normalize(group={"default"})
     */
    private ?int $number = null;

    /**
     * @Bos\Normalize(group={"default"})
     */
    private ?string $postalCode = null;

    /**
     * @Bos\Normalize(group={"default"}, callback="getCityWithFormat")
     */
    private ?string $city = null;

    /**
     * @Bos\Normalize(group={"maxDepthTestDepth1"}, type="collection")
     */
    private ?Collection $group = null;

    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @return $this
     */
    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @return $this
     */
    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @return $this
     */
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getCityWithFormat(): string
    {
        return 'The City Is: ' . $this->city;
    }

    /**
     * @return $this
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @Bos\Normalize(type="collection", name="group", group={"maxDepthTestDepth1OnMethod"})
     */
    public function getGroup(): ?Collection
    {
        return $this->group;
    }

    /**
     * @return $this
     */
    public function setGroup(Collection $group): self
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
