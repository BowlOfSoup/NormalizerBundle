<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

/**
 * Test class using PHP 8 attributes instead of docblock annotations.
 */
#[Bos\Normalize(group: ['default', 'api', 'admin'])]
class AddressWithAttributes
{
    #[Bos\Normalize(group: ['default', 'api', 'admin'])]
    private ?int $id = null;

    #[Bos\Normalize(group: ['default', 'api', 'admin'])]
    private ?string $street = null;

    #[Bos\Normalize(group: ['default', 'api', 'admin'])]
    private ?int $number = null;

    #[Bos\Normalize(group: ['default', 'api', 'admin'])]
    private ?string $city = null;

    #[Bos\Normalize(group: ['default', 'api', 'admin'])]
    private ?string $postalCode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    #[Bos\Normalize(group: ['default', 'api', 'admin'], name: 'fullAddress')]
    public function getFullAddress(): string
    {
        return sprintf(
            '%s %s, %s %s',
            $this->street ?? '',
            $this->number ?? '',
            $this->postalCode ?? '',
            $this->city ?? ''
        );
    }
}
