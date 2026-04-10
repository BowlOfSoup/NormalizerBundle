<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

/**
 * @Bos\Normalize(group={"default"}, skipEmpty=true)
 */
class Social
{
    private ?int $id = null;

    /**
     * @Bos\Normalize(group={"default"})
     */
    private ?string $facebook = null;

    /**
     * @Bos\Normalize(group={"default"})
     */
    private ?string $twitter = null;

    /**
     * @Bos\Normalize(group={"default"})
     */
    private ?string $instagram = null;

    /**
     * @Bos\Normalize(group={"default"})
     */
    private ?string $snapchat = null;

    /**
     * Used to show circular reference for the normalizer.
     *
     * @Bos\Normalize(group={"default"}, type="object")
     */
    private ?Person $person = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @Bos\Normalize(name="facebook", group={"proxy-method"})
     */
    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    /**
     * @return $this
     */
    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    /**
     * @return $this
     */
    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    /**
     * @return $this
     */
    public function setInstagram(?string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getSnapchat(): ?string
    {
        return $this->snapchat;
    }

    /**
     * @return $this
     */
    public function setSnapchat(?string $snapchat): self
    {
        $this->snapchat = $snapchat;

        return $this;
    }

    /**
     * @Bos\Normalize(type="object", group={"circRefMethod"})
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @return $this
     */
    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }
}
