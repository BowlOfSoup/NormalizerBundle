<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Common\Collections\Collection;

/**
 * Test class using PHP 8 attributes instead of docblock annotations.
 */
#[Bos\Serialize(wrapElement: 'data', group: ['default'], sortProperties: true)]
class PersonWithAttributes
{
    #[Bos\Normalize(group: ['default', 'api'])]
    private ?int $id = null;

    #[Bos\Normalize(name: 'full_name', group: ['default'])]
    private ?string $name = null;

    #[Bos\Normalize(group: ['default', 'api'])]
    private ?string $email = null;

    #[Bos\Normalize(group: ['default'], type: 'DateTime', format: 'Y-m-d')]
    private ?\DateTime $birthDate = null;

    #[Bos\Normalize(group: ['default'], type: 'collection')]
    private ?Collection $addresses = null;

    #[Bos\Normalize(group: ['default'], type: 'object')]
    private ?AddressWithAttributes $primaryAddress = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getAddresses(): ?Collection
    {
        return $this->addresses;
    }

    public function setAddresses(?Collection $addresses): self
    {
        $this->addresses = $addresses;

        return $this;
    }

    public function getPrimaryAddress(): ?AddressWithAttributes
    {
        return $this->primaryAddress;
    }

    public function setPrimaryAddress(?AddressWithAttributes $primaryAddress): self
    {
        $this->primaryAddress = $primaryAddress;

        return $this;
    }

    #[Bos\Normalize(group: ['default'], name: 'birthYear', type: 'DateTime', format: 'Y')]
    public function getBirthYear(): ?\DateTime
    {
        return $this->birthDate;
    }

    #[Bos\Normalize(group: ['default'], name: 'contactEmail')]
    public function getContactEmail(): ?string
    {
        return $this->email;
    }
}
