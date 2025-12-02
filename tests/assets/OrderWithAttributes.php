<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Test class demonstrating complex scenarios with PHP 8 attributes:
 * - Collections with callbacks
 * - Nested objects
 * - Skip empty fields
 * - Multiple groups
 */
#[Bos\Serialize(wrapElement: 'order', group: ['api', 'admin'])]
class OrderWithAttributes
{
    #[Bos\Normalize(group: ['api', 'admin'])]
    private ?int $id = null;

    #[Bos\Normalize(group: ['api', 'admin'], name: 'order_number')]
    private ?string $orderNumber = null;

    #[Bos\Normalize(group: ['api', 'admin'], type: 'DateTime', format: 'c')]
    private ?\DateTime $orderDate = null;

    #[Bos\Normalize(group: ['api', 'admin'], type: 'collection')]
    private ?Collection $items = null;

    #[Bos\Normalize(group: ['api', 'admin'], type: 'object')]
    private ?AddressWithAttributes $shippingAddress = null;

    #[Bos\Normalize(group: ['admin'], skipEmpty: true)]
    private ?string $internalNotes = null;

    #[Bos\Normalize(group: ['api', 'admin'], name: 'customer_email', skipEmpty: true)]
    private ?string $email = null;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): self
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getOrderDate(): ?\DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(?\DateTime $orderDate): self
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getItems(): ?Collection
    {
        return $this->items;
    }

    public function setItems(?Collection $items): self
    {
        $this->items = $items;

        return $this;
    }

    public function addItem(ProductWithAttributes $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }

        return $this;
    }

    public function getShippingAddress(): ?AddressWithAttributes
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?AddressWithAttributes $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    public function getInternalNotes(): ?string
    {
        return $this->internalNotes;
    }

    public function setInternalNotes(?string $internalNotes): self
    {
        $this->internalNotes = $internalNotes;

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

    #[Bos\Normalize(group: ['api', 'admin'], name: 'total_items')]
    public function getTotalItems(): int
    {
        return $this->items ? $this->items->count() : 0;
    }

    #[Bos\Normalize(group: ['api', 'admin'], name: 'order_status')]
    public function getStatus(): string
    {
        if (null === $this->orderDate) {
            return 'draft';
        }

        $now = new \DateTime();
        $diff = $now->diff($this->orderDate)->days;

        if ($diff < 1) {
            return 'new';
        }

        if ($diff < 7) {
            return 'processing';
        }

        return 'shipped';
    }
}
