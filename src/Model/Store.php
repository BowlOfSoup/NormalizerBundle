<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Model;

class Store
{
    /** @var mixed[] */
    private $values = [];

    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function set(string $key, $value = null): self
    {
        $this->values[$key] = $value;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function get(string $key)
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
