<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use BowlOfSoup\NormalizerBundle\Tests\ArraySubset;
use BowlOfSoup\NormalizerBundle\Tests\assets\Address;
use BowlOfSoup\NormalizerBundle\Tests\assets\Group;
use BowlOfSoup\NormalizerBundle\Tests\assets\Hobbies;
use BowlOfSoup\NormalizerBundle\Tests\assets\HobbyType;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use BowlOfSoup\NormalizerBundle\Tests\assets\ProxyObject;
use BowlOfSoup\NormalizerBundle\Tests\assets\ProxySocial;
use BowlOfSoup\NormalizerBundle\Tests\assets\Social;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use BowlOfSoup\NormalizerBundle\Tests\assets\TelephoneNumbers;
use BowlOfSoup\NormalizerBundle\Tests\NormalizerTestTrait;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class NormalizerTest extends TestCase
{
    use NormalizerTestTrait;

    private Normalizer $normalizer;

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
     * @testdox Normalize an integer, but only objects or an array of objects are allowed.
     */
    public function testNormalizeInvalidDataType()
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Can only normalize an object or an array of objects. Input contains: integer');

        $this->normalizer->normalize([
            [
                'value' => 123,
            ],
            [
                'foo' => 'bar',
            ],
        ]);
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
            'addresses' => [
                [
                    'getSpecialNotesForDelivery' => 'some special string',
                ],
                [
                    'getSpecialNotesForDelivery' => 'some special string',
                ],
            ],
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
            'dateOfBirth' => '1980-01-01',
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

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @testdox Normalize object, null object given.
     */
    public function testNormalizeNullObject(): void
    {
        $someClass = null;

        $result = $this->normalizer->normalize($someClass);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @testdox Normalize object, Circular reference, no fallback.
     */
    public function testNormalizeCircularReferenceNoFallback(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Circular reference on: BowlOfSoup\NormalizerBundle\Tests\assets\Person called from: BowlOfSoup\NormalizerBundle\Tests\assets\Social. If possible, prevent this by adding a getId() method to BowlOfSoup\NormalizerBundle\Tests\assets\Person');

        /* @var \BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor $classExtractor */
        $this->classExtractor = $this
            ->getMockBuilder(ClassExtractor::class)
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
     * @testdox Normalize object, Circular reference, no fallback.
     */
    public function testNormalizeCircularReferenceNoFallbackOnMethods(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Circular reference on: BowlOfSoup\NormalizerBundle\Tests\assets\Person called from: BowlOfSoup\NormalizerBundle\Tests\assets\Social. If possible, prevent this by adding a getId() method to BowlOfSoup\NormalizerBundle\Tests\assets\Person');

        /* @var \BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor $classExtractor */
        $this->classExtractor = $this
            ->getMockBuilder(ClassExtractor::class)
            ->setMethods(['getId'])
            ->getMock();
        $this->classExtractor
            ->expects($this->any())
            ->method('getId')
            ->willReturn(null);

        $person = $this->getDummyDataSet();

        $normalizer = $this->getNormalizer();
        $normalizer->normalize($person, 'circRefMethod');
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
     * @testdox Normalize object, with limited depth to 0, with type: object.
     */
    public function testNormalizeSuccessMaxDepth0AndTypeObject(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'maxDepthTestDepth0OnMethodWithObject');

        $expectedResult = [
            'getSocial' => 546,
        ];

        $this->assertNotEmpty($result);
        ArraySubset::assert($expectedResult, $result);
    }

    /**
     * @testdox Normalize object, with limited depth to 0, on a method
     */
    public function testNormalizeSuccessMaxDepth0OnMethod(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('Maximal depth reached, but no identifier found. Prevent this by adding a getId() method to BowlOfSoup\NormalizerBundle\Tests\assets\Address');

        $person = $this->getDummyDataSet();

        $this->assertEmpty($this->normalizer->normalize($person, 'maxDepthTestDepth0OnMethod'));
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
     * @testdox Normalize object, with limited depth to 1.
     */
    public function testNormalizeSuccessMaxDepth1OnMethod(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'maxDepthTestDepth1OnMethod');

        $expectedResult = [
            'getAddresses' => [
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
            'getTelephoneNumbers' => null,
        ];

        $this->assertNotEmpty($result);
        $this->assertSame($result, $expectedResult);
    }

    /**
     * @testdox Normalize object, ISSUE-17 scenario, fallback for DateTime.
     */
    public function testNormalizeFallbackDateTime(): void
    {
        $person = $this->getDummyDataSet();

        $result = $this->normalizer->normalize($person, 'dateTimeTest');

        $this->assertNotEmpty($result);
        ArraySubset::assert($result, [
            'deceasedDate' => 'Jan. 2020',
            'calculateDeceasedDate' => 'Jan. 2020',
        ]);
    }

    /**
     * @testdox Not possible to normalize a method using a callback.
     */
    public function testCallbackOnMethod(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('A callback is set on method calculateDeceasedDate. Callbacks are not allowed on methods.');

        $person = $this->getDummyDataSet();

        $this->normalizer->normalize($person, 'methodWithCallback');
    }

    /**
     * @testdox Not possible to normalize a method with no type configured, and a callback.
     */
    public function testCallbackOnMethodWithNoType(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('A callback is set on method calculateDeceasedDate2. Callbacks are not allowed on methods.');

        $person = $this->getDummyDataSet();

        $this->normalizer->normalize($person, 'methodWithCallbackAndNoType');
    }

    /**
     * @testdox Not possible to normalize a method with type object configured, and a callback.
     */
    public function testCallbackOnMethodWithTypeObject(): void
    {
        $this->expectException(BosNormalizerException::class);
        $this->expectExceptionMessage('A callback is set on method getSocial. Callbacks are not allowed on methods.');

        $person = $this->getDummyDataSet();

        $this->normalizer->normalize($person, 'callbackOnMethodWithObject');
    }

    /**
     * @testdox Try to normalize a DateTime type, which is not a \DateTime.
     */
    public function testNormalizeDateTimeString(): void
    {
        $person = $this->getDummyDataSet();

        $this->assertSame(
            ['calculateDeceasedDateAsString' => null],
            $this->normalizer->normalize($person, 'dateTimeStringTest')
        );
    }

    public function testDontOverwriteChildConstructWithParent(): void
    {
        $person = (new Person())
            ->setName('child-foo')
            ->setDateOfBirth(new \DateTime('1987-11-17'));

        $this->assertSame([
            'name' => 'child-foo',
            'getDateOfBirth' => '1987-11-17',
        ], $this->normalizer->normalize($person, 'parent_test'));
    }

    /**
     * @testdox Test an object being cached.
     */
    public function testObjectBeingCached(): void
    {
        $person = $this->getDummyDataSetWithDuplicateObjectById();

        $result = $this->normalizer->normalize($person, 'duplicateObjectId');

        $this->assertSame([
            'hobbies' => [
                [
                    'id' => 1,
                    'description' => 'Fixing washing machines',
                    'hobbyType' => [
                        'id' => 1,
                        'name' => 'Technical',
                    ],
                ],
                [
                    'id' => 2,
                    'description' => 'Volleyball',
                    'hobbyType' => [
                        'id' => 2,
                        'name' => 'Sport',
                    ],
                ],
                [
                    'id' => 3,
                    'description' => 'Fixing Computers',
                    'hobbyType' => [
                        'id' => 1,
                        'name' => 'Technical',
                    ],
                ],
            ],
        ], $result);
    }

    /**
     * @testdox Try to normalize a method with an object type, which does not hold any value.
     */
    public function testNormalizeMethodWithObjectAndAnEmptyValue(): void
    {
        $person = $this->getDummyDataSet();

        $this->assertSame(
            ['thisHoldsNoValue' => null],
            $this->normalizer->normalize($person, 'emptyObjectOnMethod')
        );
    }

    public function testNormalizeWithTranslation(): void
    {
        $person = $this->getDummyDataSet();
        $person->setGender('male');

        $result = $this->normalizer->normalize($person, 'translation');

        $this->assertSame([
            'id' => 123,
            'gender' => 'translatedValue',
            'translateMeThis' => 'translatedValue',
        ], $result);
    }

    public function testNormalizeProxyWithMethods(): void
    {
        $socialProxy = new ProxySocial();
        $socialProxy->setFacebook('foo');

        $this->assertSame([
            'facebook' => 'foo',
        ], $this->normalizer->normalize($socialProxy, 'proxy-method'));
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
        $hobbies1->setId(1);
        $hobbies1->setDescription('Playing Guitar');
        $hobbies1->setHobbyType($hobbyType1);
        $hobbyCollection->add($hobbies1);

        $hobbies2 = new Hobbies();
        $hobbies2->setId(2);
        $hobbies2->setDescription('Fixing Computers');
        $hobbies2->setHobbyType($hobbyType2);
        $hobbyCollection->add($hobbies2);

        $hobbies3 = new Hobbies();
        $hobbies3->setId(3);
        $hobbies3->setDescription('Playing Piano');
        $hobbies3->setHobbyType($hobbyType1);
        $hobbyCollection->add($hobbies3);

        $person->setHobbies($hobbyCollection);

        $person->setTestForProxy(new ProxyObject());

        return $person;
    }

    private function getDummyDataSetWithDuplicateObjectById(): Person
    {
        $person = new Person();
        $person
            ->setId(123)
            ->setName('Bowl')
            ->setSurName('Of Soup')
            ->setDateOfBirth(new \DateTime('1980-01-01'))
            ->setValidCollectionPropertyWithCallback([new SomeClass()]);

        $hobbyCollection = new ArrayCollection();

        $hobbyType = new HobbyType();
        $hobbyType->setId(1);
        $hobbyType->setName('Technical');

        $hobbyType2 = new HobbyType();
        $hobbyType2->setId(2);
        $hobbyType2->setName('Sport');

        $hobbies1 = new Hobbies();
        $hobbies1->setId(1);
        $hobbies1->setDescription('Fixing washing machines');
        $hobbies1->setHobbyType($hobbyType);
        $hobbyCollection->add($hobbies1);

        $hobbies2 = new Hobbies();
        $hobbies2->setId(2);
        $hobbies2->setDescription('Volleyball');
        $hobbies2->setHobbyType($hobbyType2);
        $hobbyCollection->add($hobbies2);

        $hobbies3 = new Hobbies();
        $hobbies3->setId(3);
        $hobbies3->setDescription('Fixing Computers');
        $hobbies3->setHobbyType($hobbyType);
        $hobbyCollection->add($hobbies3);

        $person->setHobbies($hobbyCollection);

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
                    'getSpecialNotesForDelivery' => 'some special string',
                ],
                [
                    'street' => null,
                    'number' => 4,
                    'postalCode' => '1234AB',
                    'city' => 'The City Is: ',
                    'getSpecialNotesForDelivery' => 'some special string',
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
                    'id' => 1,
                    'description' => 'Playing Guitar',
                    'hobbyType' => [
                        'id' => 1,
                        'name' => 'Music',
                    ],
                ],
                [
                    'id' => 2,
                    'description' => 'Fixing Computers',
                    'hobbyType' => [
                        'id' => 2,
                        'name' => 'Technical',
                    ],
                ],
                [
                    'id' => 3,
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
                    'getSpecialNotesForDelivery' => 'some special string',
                ],
                [
                    'street' => null,
                    'number' => 4,
                    'postalCode' => '1234AB',
                    'city' => 'The City Is: ',
                    'getSpecialNotesForDelivery' => 'some special string',
                ],
            ],
            'testForNormalizingCallbackObject' => [
                'street' => 'Dummy Street',
                'number' => null,
                'postalCode' => null,
                'city' => 'The City Is: Amsterdam',
                'getSpecialNotesForDelivery' => 'some special string',
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
