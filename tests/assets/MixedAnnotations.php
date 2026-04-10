<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

/**
 * Test class that mixes docblock annotations with PHP 8 attributes.
 *
 * @Bos\Serialize(wrapElement="mixed", group={"docblock"})
 */
#[Bos\Serialize(wrapElement: 'attributes', group: ['attribute'])]
class MixedAnnotations
{
    /**
     * This property uses docblock annotation.
     *
     * @Bos\Normalize(group={"docblock", "default"})
     */
    private ?int $idFromDocblock = null;

    /**
     * This property uses PHP 8 attribute.
     */
    #[Bos\Normalize(group: ['attribute', 'default'])]
    private ?string $nameFromAttribute = null;

    /**
     * This property has BOTH docblock and attribute (attribute should take precedence).
     *
     * @Bos\Normalize(group={"docblock"}, name="old_name")
     */
    #[Bos\Normalize(name: 'new_name', group: ['attribute', 'default'])]
    private ?string $dualAnnotated = null;

    public function getIdFromDocblock(): ?int
    {
        return $this->idFromDocblock;
    }

    public function setIdFromDocblock(?int $idFromDocblock): self
    {
        $this->idFromDocblock = $idFromDocblock;

        return $this;
    }

    public function getNameFromAttribute(): ?string
    {
        return $this->nameFromAttribute;
    }

    public function setNameFromAttribute(?string $nameFromAttribute): self
    {
        $this->nameFromAttribute = $nameFromAttribute;

        return $this;
    }

    public function getDualAnnotated(): ?string
    {
        return $this->dualAnnotated;
    }

    public function setDualAnnotated(?string $dualAnnotated): self
    {
        $this->dualAnnotated = $dualAnnotated;

        return $this;
    }

    /**
     * This method uses docblock annotation.
     *
     * @Bos\Normalize(group={"docblock", "default"}, type="DateTime", format="Y-m-d")
     */
    public function getCreatedAtDocblock(): \DateTime
    {
        return new \DateTime('2024-01-01');
    }

    /**
     * This method uses PHP 8 attribute.
     */
    #[Bos\Normalize(group: ['attribute', 'default'], type: 'DateTime', format: 'Y-m-d H:i:s')]
    public function getCreatedAtAttribute(): \DateTime
    {
        return new \DateTime('2024-01-01 12:00:00');
    }
}
