<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Tests\ArraySubset;
use BowlOfSoup\NormalizerBundle\Tests\assets\Address;
use BowlOfSoup\NormalizerBundle\Tests\assets\Group;
use BowlOfSoup\NormalizerBundle\Tests\assets\Hobbies;
use BowlOfSoup\NormalizerBundle\Tests\assets\HobbyType;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use BowlOfSoup\NormalizerBundle\Tests\assets\ProxyObject;
use BowlOfSoup\NormalizerBundle\Tests\assets\Social;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use BowlOfSoup\NormalizerBundle\Tests\assets\TelephoneNumbers;
use BowlOfSoup\NormalizerBundle\Tests\NormalizerTestTrait;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class NormalizerTest extends TestCase
{
    use NormalizerTestTrait;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalizer */
    private $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = $this->getNormalizer();
    }

    /**
     * @testdox Normalize object, full happy path no type property, still callback
     *
     * Occurrence of assertArraySubset, which is going to be deprecated. If upgrade to PHPUnit 9, use: dms/phpunit-arraysubset-asserts.
     */
    public function testNormalizeSuccess(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'default');

        $expectedResult = $this->getSuccessResult();

        $this->assertNotEmpty($result);
        ArraySubset::assert($result, $expectedResult);
    }

    /**
     * @testdox Normalize array of objects, full happy path no type property, still callback
     */
    public function testNormalizeArraySuccess(): void
    {
        $arrayOfObjects = [$this->getDummyDataSet(), $this->getDummyDataSet()];

        $result = $this->normalizer->normalize($arrayOfObjects, 'default');

        $expectedResult = $this->getSuccessResult();

        $this->assertNotEmpty($result);
        ArraySubset::assert($result[0], $expectedResult);
        ArraySubset::assert($result[1], $expectedResult);
    }

    /**
     * @testdox Normalize object, normalize different group, different output.
     */
    public function testNormalizeSuccessDifferentGroup(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'anotherGroup');

        $expectedResult = [
            'surName' => 'Of Soup',
        ];

        $this->assertNotEmpty($result);
        ArraySubset::assert($result, $expectedResult);
    }

    /**
     * @testdox Normalize object, normalize with no group specified.
     */
    public function testNormalizeSuccessNoGroup(): void
    {
        $person = $this->getDummyDataSet();
        $person->setGender('male');

        $result = $this->normalizer->normalize($person);

        $expectedResult = [
            'gender' => 'male',
        ];

        $this->assertNotEmpty($result);
        ArraySubset::assert($result, $expectedResult);
    }

    /**
     * @testdox Normalize object, no matching group.
     */
    public function testNormalizeNoGroupMatch(): void
    {
        $result = $this->normalizer->normalize(new Person(), 'SomeUnknownGroup');

        $this->assertSame(gettype([]), gettype($result));
        $this->assertEmpty($result);
    }

    /**
     * @testdox Normalize object, no annotations on object.
     */
    public function testNormalizeNoAnnotations(): void
    {
        $someClass = new SomeClass();

        $result = $this->normalizer->normalize($someClass);

        $this->assertSame(gettype([]), gettype($result));
        $this->assertEmpty($result);
    }

    /**
     * @testdox Normalize object, null object given.
     */
    public function testNormalizeNullObject(): void
    {
        $someClass = null;

        $result = $this->normalizer->normalize($someClass);

        $this->assertSame(gettype([]), gettype($result));
        $this->assertEmpty($result);
    }

    /**
     * @testdox Normalize object, Circular reference, no fallback (hack!).
     */
    public function testNormalizeCircularReferenceNoFallback(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Circular reference on: BowlOfSoup\NormalizerBundle\Tests\assets\Person called from: BowlOfSoup\NormalizerBundle\Tests\assets\Social. If possible, prevent this by adding a getId() method to BowlOfSoup\NormalizerBundle\Tests\assets\Person');

        /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor $classExtractor */
        $this->classExtractor = $this
            ->getMockBuilder(ClassExtractor::class)
            ->setConstructorArgs([new AnnotationReader()])
            ->setMethods(['getId'])
            ->getMock();
        $this->classExtractor
            ->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        $person = $this->getDummyDataSet();

        $normalizer = $this->getNormalizer();
        $normalizer->normalize($person, 'default');
    }

    /**
     * @testdox Normalize object, with limited depth to 0.
     */
    public function testNormalizeSuccessMaxDepth0(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'maxDepthTestDepth0');

        $expectedResult = [
            'social' => '546',
        ];

        $this->assertNotEmpty($result);
        ArraySubset::assert($result, $expectedResult);
    }

    /**
     * @testdox Normalize object, with limited depth to 1.
     */
    public function testNormalizeSuccessMaxDepth1(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'maxDepthTestDepth1');

        $expectedResult = [
            'addresses' => [
                [
                    'group' => [
                        786,
                        346,
                    ],
                ],
                [
                    'group' => [
                        786,
                        346,
                    ],
                ],
            ],
        ];

        $this->assertNotEmpty($result);
        ArraySubset::assert($result, $expectedResult);
    }

    /**
     * @testdox Normalize object, with limited depth to 0, but no identifier method.
     */
    public function testNormalizeSuccessMaxDepth0NoIdentifier(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Maximal depth reached, but no identifier found. Prevent this by adding a getId() method to BowlOfSoup\NormalizerBundle\Tests\assets\Address');

        $person = $this->getDummyDataSet();

        $this->normalizer->normalize($person, 'maxDepthTestDepthNoIdentifier');
    }

    /**
     * @testdox Normalize object, no content in collection, because group is not in collection/object.
     */
    public function testNormalizeNoContentForCollection(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'noContentForCollectionTest');

        $expectedResult = [
            'addresses' => [
                null,
                null,
            ],
            'social' => null,
        ];

        $this->assertNotEmpty($result);
        $this->assertSame($result, $expectedResult);
    }

    /**
     * @testdox Normalize object, issue-17 scenario, fallback for DateTime.
     */
    public function testNormalizeFallbackDateTime(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'dateTimeTest');

        $this->assertNotEmpty($result);
        ArraySubset::assert($result, ['deceasedDate' => 'Jan. 2020']);
    }

    private function getDummyDataSet(): Person
    {
        $groupCollection = new ArrayCollection();
        $group1 = new Group();
        $group1->setId(786);
        $group1->setName('Dummy Name');
        $groupCollection->add($group1);
        $group2 = new Group();
        $group2->setId(346);
        $group2->setName('Another Dummy Name');
        $groupCollection->add($group2);

        $person = new Person();
        $person
            ->setId(123)
            ->setName('Bowl')
            ->setSurName('Of Soup')
            ->setDateOfBirth(new \DateTime('1980-01-01'))
            ->setValidCollectionPropertyWithCallback([new SomeClass()]);

        $social = new Social();
        $social
            ->setId(546)
            ->setFacebook('Facebook ID')
            ->setTwitter('Twitter ID')
            ->setPerson($person);
        $person->setSocial($social);

        $addressCollection = new ArrayCollection();
        $address1 = new Address();
        $address1
            ->setStreet('Dummy Street')
            ->setCity('Amsterdam')
            ->setGroup($groupCollection);
        $addressCollection->add($address1);
        $address2 = new Address();
        $address2
            ->setPostalCode('1234AB')
            ->setNumber(4)
            ->setGroup($groupCollection);
        $addressCollection->add($address2);
        $person->setAddresses($addressCollection);

        $telephoneNumbers = new TelephoneNumbers();
        $telephoneNumbers
            ->setHome(123)
            ->setMobile(456)
            ->setWork(789)
            ->setWife(777);
        $person->setTelephoneNumbers($telephoneNumbers);

        $hobbyCollection = new ArrayCollection();

        $hobbyType1 = new HobbyType();
        $hobbyType1->setId(1);
        $hobbyType1->setName('Music');

        $hobbyType2 = new HobbyType();
        $hobbyType2->setId(2);
        $hobbyType2->setName('Technical');

        $hobbies1 = new Hobbies();
        $hobbies1->setDescription('Playing Guitar');
        $hobbies1->setHobbyType($hobbyType1);
        $hobbyCollection->add($hobbies1);

        $hobbies2 = new Hobbies();
        $hobbies2->setDescription('Fixing Computers');
        $hobbies2->setHobbyType($hobbyType2);
        $hobbyCollection->add($hobbies2);

        $hobbies3 = new Hobbies();
        $hobbies3->setDescription('Playing Piano');
        $hobbies3->setHobbyType($hobbyType1);
        $hobbyCollection->add($hobbies3);

        $person->setHobbies($hobbyCollection);

        $person->setTestForProxy(new ProxyObject());

        return $person;
    }

    private function getSuccessResult(): array
    {
        return [
            'id' => 123,
            'name_value' => 'Bowl',
            'surName' => 'Of Soup',
            'initials' => null,
            'dateOfBirth' => '1980-01-01',
            'dateOfRegistration' => 'Apr. 2015',
            'addresses' => [
                [
                    'street' => 'Dummy Street',
                    'number' => null,
                    'postalCode' => null,
                    'city' => 'The City Is: Amsterdam',
                ],
                [
                    'street' => null,
                    'number' => 4,
                    'postalCode' => '1234AB',
                    'city' => 'The City Is: ',
                ],
            ],
            'social' => [
                'facebook' => 'Facebook ID',
                'twitter' => 'Twitter ID',
                'person' => [
                    'id' => 123,
                ],
            ],
            'telephoneNumbers' => [
                'home' => 123,
                'mobile' => 456,
                'work' => 789,
                'wife' => 777,
            ],
            'hobbies' => [
                [
                    'description' => 'Playing Guitar',
                    'hobbyType' => [
                        'id' => 1,
                        'name' => 'Music',
                    ],
                ],
                [
                    'description' => 'Fixing Computers',
                    'hobbyType' => [
                        'id' => 2,
                        'name' => 'Technical',
                    ],
                ],
                [
                    'description' => 'Playing Piano',
                    'hobbyType' => [
                        'id' => 1,
                        'name' => 'Music',
                    ],
                ],
            ],
            'nonValidCollectionProperty' => null,
            'validCollectionPropertyWithCallback' => [123],
            'validEmptyObjectProperty' => null,
            'testForNormalizingCallback' => [
                [
                    'street' => 'Dummy Street',
                    'number' => null,
                    'postalCode' => null,
                    'city' => 'The City Is: Amsterdam',
                ],
                [
                    'street' => null,
                    'number' => 4,
                    'postalCode' => '1234AB',
                    'city' => 'The City Is: ',
                ],
            ],
            'testForNormalizingCallbackObject' => [
                'street' => 'Dummy Street',
                'number' => null,
                'postalCode' => null,
                'city' => 'The City Is: Amsterdam',
            ],
            'testForNormalizingCallbackString' => 'asdasd',
            'testForNormalizingCallbackArray' => [
                '123',
                '456',
                '789',
            ],
            'testForProxy' => [
                'value' => 'Hello',
            ],
        ];
    }
}
