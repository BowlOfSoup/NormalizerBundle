<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Model;

class Store
{
    private array $values = [];

    public function set(string $key, mixed $value = null): self
    {
        $this->values[$key] = $value;

        return $this;
    }

    public function get(string $key): mixed
    {
        if (!$this->has($key)) {
            return null;
        }

        return $this->values[$key];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }
}
