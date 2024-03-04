<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Model;

class Context
{
    /** @var string|null */
    private $group;

    /** @var string[] */
    private $includes = [];

    /** @var int|null */
    private $maxDepth;

    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @return $this
     */
    public function setGroup(?string $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getIncludes(): array
    {
        return $this->includes;
    }

    /**
     * @return $this
     */
    public function setIncludes(array $includes): self
    {
        $this->includes = $includes;

        return $this;
    }

    /**
     * @return $this
     */
    public function setIncludesFromString(string $includes): self
    {
        $this->setIncludes(explode(',', $includes));

        return $this;
    }

    /**
     * @return $this
     */
    public function addInclude(string $include): self
    {
        $this->includes[] = $include;

        return $this;
    }

    public function hasInclude(string $assertion): bool
    {
        return in_array($assertion, $this->includes);
    }

    public function hasIncludes(): bool
    {
        return count($this->includes) > 0;
    }

    public function getMaxDepth(): ?int
    {
        return $this->maxDepth;
    }

    /**
     * @return $this
     */
    public function setMaxDepth(int $maxDepth): self
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }
}