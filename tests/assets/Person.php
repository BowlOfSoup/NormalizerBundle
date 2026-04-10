<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;
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
     * @Bos\Normalize(group={"default", "translation"})
     *
     * @Bos\Translate(group={"translation"})
     */
    private ?int $id = null;

    /**
     * @Bos\Normalize(group={"default"}, name="name_value")
     * @Bos\Normalize(group={"parent_test"})
     */
    private ?string $name = null;

    /**
     * @Bos\Normalize(group={"default", "anotherGroup"})
     */
    private ?string $surName = null;

    /**
     * @Bos\Normalize(group={"default"}, type="collection")
     */
    private ?string $initials = null;

    /**
     * @Bos\Normalize()
     * @Bos\Normalize(group={"translation"})
     *
     * @Bos\Translate(group={"translation"})
     */
    private ?string $gender = null;

    /**
     * @Bos\Normalize(group={"default"}, type="DateTime", format="Y-m-d")
     */
    private ?\DateTime $dateOfBirth = null;

    private ?\DateTime $dateOfRegistration = null;

    /**
     * @Bos\Normalize(group={"dateTimeTest"}, type="DateTime", format="M. Y", callback="calculateDeceasedDate")
     */
    private ?\DateTime $deceasedDate = null;

    /**
     * @Bos\Normalize(group={"default", "maxDepthTestDepth1", "maxDepthTestDepthNoIdentifier"}, type="collection")
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="collection")
     * @Bos\Normalize(group={"anotherGroup"}, type="collection")
     */
    private ?Collection $addresses = null;

    /**
     * @Bos\Normalize(group={"default", "maxDepthTestDepth0"}, type="object")
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="object")
     */
    private ?Social $social = null;

    /**
     * @Bos\Normalize(group={"default"}, type="object", callback="toArray")
     */
    private ?TelephoneNumbers $telephoneNumbers = null;

    /**
     * @Bos\Normalize(group={"default", "duplicateObjectId"}, type="collection")
     *
     * @var Hobbies[]|null
     */
    private mixed $hobbies = null;

    /**
     * @Bos\Normalize(group={"default"}, type="object")
     */
    protected mixed $validEmptyObjectProperty = null;

    /**
     * @Bos\Normalize(group={"default"}, type="collection")
     */
    protected array $nonValidCollectionProperty = ['123', '456'];

    /**
     * @Bos\Normalize(group={"default"}, type="collection", callback="getProperty32")
     */
    private ?array $validCollectionPropertyWithCallback = null;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallback", normalizeCallbackResult=true)
     */
    private mixed $testForNormalizingCallback = null;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallbackObject", normalizeCallbackResult=true)
     */
    private mixed $testForNormalizingCallbackObject = null;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallbackString", normalizeCallbackResult=true)
     */
    private mixed $testForNormalizingCallbackString = null;

    /**
     * @Bos\Normalize(group={"default"}, callback="getTestForNormalizingCallbackArray", normalizeCallbackResult=true)
     */
    private mixed $testForNormalizingCallbackArray = null;

    /**
     * @Bos\Normalize(group={"default"}, type="object")
     */
    private ?ProxyObject $testForProxy = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurName(): ?string
    {
        return $this->surName;
    }

    /**
     * @return $this
     */
    public function setSurName(?string $surName): self
    {
        $this->surName = $surName;

        return $this;
    }

    public function getInitials(): ?string
    {
        return $this->initials;
    }

    /**
     * @return $this
     */
    public function setInitials(?string $initials): self
    {
        $this->initials = $initials;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @return $this
     */
    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * @Bos\Normalize(type="DateTime", name="dateOfBirth")
     * @Bos\Normalize(type="DateTime", group={"parent_test"})
     */
    #[\Override]
    public function getDateOfBirth(): \DateTime
    {
        return $this->dateOfBirth;
    }

    /**
     * @return $this
     */
    public function setDateOfBirth(\DateTime $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @Bos\Normalize(group={"default"}, name="dateOfRegistration", type="DateTime", format="M. Y")
     */
    public function getDateOfRegistration(): \DateTime
    {
        if (null === $this->dateOfRegistration) {
            return new \DateTime('2015-04-23');
        }

        return $this->dateOfRegistration;
    }

    /**
     * @return $this
     */
    public function setDateOfRegistration(\DateTime $dateOfRegistration): self
    {
        $this->dateOfRegistration = $dateOfRegistration;

        return $this;
    }

    /**
     * @Bos\Normalize(group={"dateTimeTest"}, type="DateTime", format="M. Y")
     * @Bos\Normalize(group={"methodWithCallback"}, type="DateTime", format="M. Y", callback="getAddresses")
     */
    public function calculateDeceasedDate(): \DateTime
    {
        return new \DateTime('2020-01-01');
    }

    /**
     * @Bos\Normalize(group={"methodWithCallbackAndNoType"}, callback="getAddresses")
     */
    public function calculateDeceasedDate2(): \DateTime
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
     * @Bos\Normalize(type="collection", group={"maxDepthTestDepth0OnMethod"})
     * @Bos\Normalize(type="collection", group={"maxDepthTestDepth1OnMethod"})
     */
    public function getAddresses(): ?Collection
    {
        return $this->addresses;
    }

    /**
     * @return $this
     */
    public function setAddresses(Collection $addresses): self
    {
        $this->addresses = $addresses;

        return $this;
    }

    /**
     * @Bos\Normalize(type="object", group={"circRefMethod"})
     * @Bos\Normalize(type="object", group={"maxDepthTestDepth0OnMethodWithObject"})
     * @Bos\Normalize(type="object", group={"callbackOnMethodWithObject"}, callback="getAddresses")
     */
    public function getSocial(): ?Social
    {
        return $this->social;
    }

    /**
     * @return $this
     */
    public function setSocial(Social $social): self
    {
        $this->social = $social;

        return $this;
    }

    /**
     * @Bos\Normalize(group={"noContentForCollectionTest"}, type="object")
     */
    public function getTelephoneNumbers(): ?TelephoneNumbers
    {
        return $this->telephoneNumbers;
    }

    /**
     * @return $this
     */
    public function setTelephoneNumbers(TelephoneNumbers $telephoneNumbers): self
    {
        $this->telephoneNumbers = $telephoneNumbers;

        return $this;
    }

    /**
     * @return Hobbies[]
     */
    public function getHobbies(): array
    {
        return $this->hobbies;
    }

    /**
     * @param Hobbies[] $hobbies
     *
     * @return $this
     */
    public function setHobbies(mixed $hobbies): self
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    /**
     * @return $this
     */
    public function setValidCollectionPropertyWithCallback(array $validCollectionPropertyWithCallback): self
    {
        $this->validCollectionPropertyWithCallback = $validCollectionPropertyWithCallback;

        return $this;
    }

    public function normalizeValidCollectionPropertyWithCallback(): string
    {
        return 'test';
    }

    public function getTestForNormalizingCallback(): ArrayCollection
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

    public function getTestForNormalizingCallbackObject(): Address
    {
        $address1 = new Address();
        $address1->setStreet('Dummy Street');
        $address1->setCity('Amsterdam');

        return $address1;
    }

    public function getTestForNormalizingCallbackString(): string
    {
        return 'asdasd';
    }

    public function getTestForNormalizingCallbackArray(): array
    {
        return [
            '123',
            '456',
            '789',
        ];
    }

    public function getTestForProxy(): ?ProxyObject
    {
        return $this->testForProxy;
    }

    public function setTestForProxy(ProxyObject $proxyObject): void
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
