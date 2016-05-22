<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

class TelephoneNumbers
{
    /** @var string */
    private $home;

    /** @var string */
    private $mobile;

    /** @var string */
    private $work;

    /** @var string */
    private $wife;

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'home' => $this->home,
            'mobile' => $this->mobile,
            'work' => $this->work,
            'wife' => $this->wife,
        );
    }

    /**
     * @param string $home
     *
     * @return $this
     */
    public function setHome($home)
    {
        $this->home = $home;

        return $this;
    }

    /**
     * @param string $mobile
     *
     * @return $this
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @param string $work
     *
     * @return $this
     */
    public function setWork($work)
    {
        $this->work = $work;

        return $this;
    }

    /**
     * @param string $wife
     *
     * @return $this
     */
    public function setWife($wife)
    {
        $this->wife = $wife;

        return $this;
    }
}
