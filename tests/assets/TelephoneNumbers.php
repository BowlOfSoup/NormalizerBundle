<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

class TelephoneNumbers
{
    private string|int $home;
    private string|int $mobile;
    private string|int $work;
    private string|int $wife;

    public function toArray(): array
    {
        return [
            'home' => $this->home,
            'mobile' => $this->mobile,
            'work' => $this->work,
            'wife' => $this->wife,
        ];
    }

    /**
     * @return $this
     */
    public function setHome(string|int $home): self
    {
        $this->home = $home;

        return $this;
    }

    /**
     * @return $this
     */
    public function setMobile(string|int $mobile): self
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @return $this
     */
    public function setWork(string|int $work): self
    {
        $this->work = $work;

        return $this;
    }

    /**
     * @return $this
     */
    public function setWife(string|int $wife): self
    {
        $this->wife = $wife;

        return $this;
    }
}
