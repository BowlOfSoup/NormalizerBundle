<?php

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use DateTime;
use Doctrine\Common\Collections\Collection;

/**
 * @Bos\Normalize(group={"maxDepthTestDepth0"}, maxDepth=0)
 * @Bos\Normalize(group={"maxDepthTestDepth1"}, maxDepth=1)
 * @Bos\Normalize(group={"maxDepthTestDepthNoIdentifier"}, maxDepth=0)
 */
class Person
{
    /**
     * @var int
     *
     * @Bos\Normalize(group={"default"})
     */
    private $id;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"}, name="name_value")
     */
    private $name;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default", "anotherGroup"})
     */
    private $surName;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"}, type="collection")
     */
    private $initials;

    /**
     * @var string
     *
     * @Bos\Normalize()
     */
    private $gender;

    /**
     * @var DateTime
     *
     * @Bos\Normalize(group={"default"}, type="DateTime", format="Y-m-d")
     */
    private $dateOfBirth;

    /**
     * @var DateTime
     *
     * @Bos\Normalize(group={"default"}, type="DateTime", format="M. Y")
     */
    private $dateOfRegistration;

    /**
     * @var Collection
     *
     * @Bos\Normalize(group={"default", "maxDepthTestDepth1", "maxDepthTestDepthNoIdentifier"}, type="collection")
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="collection")
     */
    private $addresses;

    /**
     * @var Social
     *
     * @Bos\Normalize(group={"default", "maxDepthTestDepth0"}, type="object")
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="object")
     */
    private $social;

    /**
     * @var TelephoneNumbers
     *
     * @Bos\Normalize(group={"default"}, type="object", callback="toArray")
     */
    private $telephoneNumbers;

    /**
     * @var Hobbies
     *
     * @Bos\Normalize(group={"default"}, type="collection")
     */
    private $hobbies;

    /**
     * @Bos\Normalize(group={"default"}, type="object")
     */
    protected $validEmptyObjectProperty;

    /**
     * @var array
     *
     * @Bos\Normalize(group={"default"}, type="collection")
     */
    protected $nonValidCollectionProperty = array('123', '456');

    /**
     * @Bos\Normalize(group={"default"}, type="collection", callback="getProperty32")
     */
    private $validCollectionPropertyWithCallback;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSurName()
    {
        return $this->surName;
    }

    /**
     * @param string $surName
     *
     * @return $this
     */
    public function setSurName($surName)
    {
        $this->surName = $surName;

        return $this;
    }

    /**
     * @return string
     */
    public function getInitials()
    {
        return $this->initials;
    }

    /**
     * @param string $initials
     *
     * @return $this
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;

        return $this;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     *
     * @return $this
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @param DateTime $dateOfBirth
     *
     * @return $this
     */
    public function setDateOfBirth(DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateOfRegistration()
    {
        if (null === $this->dateOfRegistration) {
            return new DateTime('2015-04-23');
        }

        return $this->dateOfRegistration;
    }

    /**
     * @param DateTime $dateOfRegistration
     *
     * @return $this
     */
    public function setDateOfRegistration(DateTime $dateOfRegistration)
    {
        $this->dateOfRegistration = $dateOfRegistration;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param Collection $addresses
     *
     * @return $this
     */
    public function setAddresses(Collection $addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @return Social
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * @param Social $social
     *
     * @return $this
     */
    public function setSocial(Social $social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * @return TelephoneNumbers
     */
    public function getTelephoneNumbers()
    {
        return $this->telephoneNumbers;
    }

    /**
     * @param TelephoneNumbers $telephoneNumbers
     *
     * @return $this
     */
    public function setTelephoneNumbers(TelephoneNumbers $telephoneNumbers)
    {
        $this->telephoneNumbers = $telephoneNumbers;

        return $this;
    }

    /**
     * @return Hobbies
     */
    public function getHobbies()
    {
        return $this->hobbies;
    }

    /**
     * @param Hobbies $hobbies
     *
     * @return $this
     */
    public function setHobbies($hobbies)
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    /**
     * @param array $validCollectionPropertyWithCallback
     *
     * @return $this
     */
    public function setValidCollectionPropertyWithCallback(array $validCollectionPropertyWithCallback)
    {
        $this->validCollectionPropertyWithCallback = $validCollectionPropertyWithCallback;

        return $this;
    }

    /**
     * @return string
     */
    public function normalizeValidCollectionPropertyWithCallback()
    {
        return 'test';
    }
}
