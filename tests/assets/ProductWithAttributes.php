<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

/**
 * Test class demonstrating translation with PHP 8 attributes.
 */
#[Bos\Serialize(wrapElement: 'product', group: ['api', 'internal'], sortProperties: true)]
class ProductWithAttributes
{
    #[Bos\Normalize(group: ['api', 'internal'])]
    private ?int $id = null;

    #[Bos\Normalize(group: ['api', 'internal'], name: 'product_name')]
    #[Bos\Translate(group: ['api'], domain: 'products', locale: 'en')]
    private ?string $name = null;

    #[Bos\Normalize(group: ['api', 'internal'])]
    #[Bos\Translate(group: ['api'], domain: 'products')]
    private ?string $description = null;

    #[Bos\Normalize(group: ['api'], type: 'DateTime', format: 'Y-m-d')]
    private ?\DateTime $createdAt = null;

    #[Bos\Normalize(group: ['internal'])]
    private ?float $cost = null;

    #[Bos\Normalize(group: ['api', 'internal'])]
    private ?float $price = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): self
    {
        $this->price = $price;

        return $this;
    }

    #[Bos\Normalize(group: ['internal'], name: 'profit_margin')]
    public function calculateProfitMargin(): ?float
    {
        if (null === $this->price || null === $this->cost || 0.0 === $this->cost) {
            return null;
        }

        return round((($this->price - $this->cost) / $this->cost) * 100, 2);
    }

    #[Bos\Normalize(group: ['api'], name: 'formatted_price')]
    public function getFormattedPrice(): ?string
    {
        if (null === $this->price) {
            return null;
        }

        return sprintf('$%.2f', $this->price);
    }
}
