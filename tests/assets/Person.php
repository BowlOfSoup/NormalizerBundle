<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @Bos\Normalize(group={"maxDepthTestDepth0"}, maxDepth=0)
 * @Bos\Normalize(group={"maxDepthTestDepth1"}, maxDepth=1)
 * @Bos\Normalize(group={"maxDepthTestDepth0OnMethod"}, maxDepth=0)
 * @Bos\Normalize(group={"maxDepthTestDepth0OnMethodWithObject"}, maxDepth=0)
 * @Bos\Normalize(group={"maxDepthTestDepth1OnMethod"}, maxDepth=1)
 * @Bos\Normalize(group={"maxDepthTestDepthNoIdentifier"}, maxDepth=0)
 *
 * @Bos\Serialize(wrapElement="wrapperElement", group={"default"}, sortProperties=true)
 */
class Person extends AbstractPerson
{
    /**
     * @var int
     *
     * @Bos\Normalize(group={"default", "translation"})
     *
     * @Bos\Translate(group={"translation"})
     */
    private $id;

    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"}, name="name_value")
     * @Bos\Normalize(group={"parent_test"})
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
     * @Bos\Normalize(group={"translation"})
     *
     * @Bos\Translate(group={"translation"})
     */
    private $gender;

    /**
     * @Bos\Normalize(group={"default"}, type="DateTime", format="Y-m-d")
     *
     * @var \DateTime|null
     */
    private $dateOfBirth = null;

    /** @var \DateTime|null */
    private $dateOfRegistration = null;

    /**
     * @var DateTime
     *
     * @Bos\Normalize(group={"dateTimeTest"}, type="DateTime", format="M. Y", callback="calculateDeceasedDate")
     */
    private $deceasedDate;

    /**
     * @Bos\Normalize(group={"default", "maxDepthTestDepth1", "maxDepthTestDepthNoIdentifier"}, type="collection")
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="collection")
     * @Bos\Normalize(group={"anotherGroup"}, type="collection")
     *
     * @var Collection|null
     */
    private $addresses = null;

    /**
     * @Bos\Normalize(group={"default", "maxDepthTestDepth0"}, type="object")
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="object")
     *
     * @var Social|null
     */
    private $social = null;

    /**
     * @Bos\Normalize(group={"default"}, type="object", callback="toArray")
     *
     * @var TelephoneNumbers|null
     */
    private $telephoneNumbers = null;

    /**
     * @var \BowlOfSoup\NormalizerBundle\Tests\assets\Hobbies[]
     *
     * @Bos\Normalize(group={"default", "duplicateObjectId"}, type="collection")
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
    protected $nonValidCollectionProperty = ['123', '456'];

    /**
     * @Bos\Normalize(group={"default"}, type="collection", callback="getProperty32")
     *
     * @var array|null
     */
    private $validCollectionPropertyWithCallback = null;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallback", normalizeCallbackResult=true)
     */
    private $testForNormalizingCallback;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallbackObject", normalizeCallbackResult=true)
     */
    private $testForNormalizingCallbackObject;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallbackString", normalizeCallbackResult=true)
     */
    private $testForNormalizingCallbackString;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallbackArray", normalizeCallbackResult=true)
     */
    private $testForNormalizingCallbackArray;

    /**
     * @Bos\Normalize(group={"default"}, type="object")
     *
     * @var ProxyObject|null
     */
    private $testForProxy = null;

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
     * @return \DateTime
     *
     * @Bos\Normalize(type="DateTime", name="dateOfBirth")
     * @Bos\Normalize(type="DateTime", group={"parent_test"})
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /**
     * @return $this
     */
    public function setDateOfBirth(DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return DateTime
     *
     * @Bos\Normalize(group={"default"}, name="dateOfRegistration", type="DateTime", format="M. Y")
     */
    public function getDateOfRegistration()
    {
        if (null === $this->dateOfRegistration) {
            return new DateTime('2015-04-23');
        }

        return $this->dateOfRegistration;
    }

    /**
     * @return $this
     */
    public function setDateOfRegistration(DateTime $dateOfRegistration)
    {
        $this->dateOfRegistration = $dateOfRegistration;

        return $this;
    }

    /**
     * @Bos\Normalize(group={"dateTimeTest"}, type="DateTime", format="M. Y")
     * @Bos\Normalize(group={"methodWithCallback"}, type="DateTime", format="M. Y", callback="getAddresses")
     */
    public function calculateDeceasedDate(): DateTime
    {
        return new \DateTime('2020-01-01');
    }

    /**
     * @Bos\Normalize(group={"methodWithCallbackAndNoType"}, callback="getAddresses")
     */
    public function calculateDeceasedDate2(): DateTime
    {
        return new \DateTime('2020-01-01');
    }

    /**
     * @Bos\Normalize(group={"dateTimeStringTest"}, type="DateTime")
     */
    public function calculateDeceasedDateAsString(): string
    {
        return '2020-01-01';
    }

    /**
     * @return Collection
     *
     * @Bos\Normalize(type="collection", group={"maxDepthTestDepth0OnMethod"})
     * @Bos\Normalize(type="collection", group={"maxDepthTestDepth1OnMethod"})
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @return $this
     */
    public function setAddresses(Collection $addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @return Social
     *
     * @Bos\Normalize(type="object", group={"circRefMethod"})
     * @Bos\Normalize(type="object", group={"maxDepthTestDepth0OnMethodWithObject"})
     * @Bos\Normalize(type="object", group={"callbackOnMethodWithObject"}, callback="getAddresses")
     */
    public function getSocial()
    {
        return $this->social;
    }

    /**
     * @return $this
     */
    public function setSocial(Social $social)
    {
        $this->social = $social;

        return $this;
    }

    /**
     * @return TelephoneNumbers
     *
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="object")
     */
    public function getTelephoneNumbers()
    {
        return $this->telephoneNumbers;
    }

    /**
     * @return $this
     */
    public function setTelephoneNumbers(TelephoneNumbers $telephoneNumbers)
    {
        $this->telephoneNumbers = $telephoneNumbers;

        return $this;
    }

    /**
     * @return \BowlOfSoup\NormalizerBundle\Tests\assets\Hobbies[]
     */
    public function getHobbies(): array
    {
        return $this->hobbies;
    }

    /**
     * @param \BowlOfSoup\NormalizerBundle\Tests\assets\Hobbies[] $hobbies
     *
     * @return $this
     */
    public function setHobbies($hobbies)
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    /**
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

    /**
     * @return ArrayCollection
     */
    public function getTestForNormalizingCallback()
    {
        $addressCollection = new ArrayCollection();
        $address1 = new Address();
        $address1->setStreet('Dummy Street');
        $address1->setCity('Amsterdam');
        $addressCollection->add($address1);
        $address2 = new Address();
        $address2->setPostalCode('1234AB');
        $address2->setNumber(4);
        $addressCollection->add($address2);

        return $addressCollection;
    }

    /**
     * @return Address
     */
    public function getTestForNormalizingCallbackObject()
    {
        $address1 = new Address();
        $address1->setStreet('Dummy Street');
        $address1->setCity('Amsterdam');

        return $address1;
    }

    /**
     * @return string
     */
    public function getTestForNormalizingCallbackString()
    {
        return 'asdasd';
    }

    /**
     * @return array
     */
    public function getTestForNormalizingCallbackArray()
    {
        return [
            '123',
            '456',
            '789',
        ];
    }

    /**
     * @return ProxyObject
     */
    public function getTestForProxy()
    {
        return $this->testForProxy;
    }

    public function setTestForProxy(ProxyObject $proxyObject)
    {
        $this->testForProxy = $proxyObject;
    }

    /**
     * @Bos\Normalize(type="object", group={"emptyObjectOnMethod"})
     */
    private function thisHoldsNoValue(): string
    {
        return '';
    }

    /**
     * @Bos\Normalize(group={"translation"})
     *
     * @Bos\Translate()
     */
    protected function translateMeThis(): string
    {
        return 'some value';
    }
}
