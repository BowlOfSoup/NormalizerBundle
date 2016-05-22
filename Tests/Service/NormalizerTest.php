<?php

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\Address;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use BowlOfSoup\NormalizerBundle\Tests\assets\Social;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use BowlOfSoup\NormalizerBundle\Tests\assets\TelephoneNumbers;
use DateTime;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit_Framework_TestCase;

class NormalizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @testdox Normalize object, full happy path no type property, still callback
     */
    public function testNormalizeSuccess()
    {
        $classExtractor = new ClassExtractor(new AnnotationReader());
        $propertyExtractor = new PropertyExtractor(new AnnotationReader());

        $person = $this->getDummyDataSet();

        $normalizer = new Normalizer($classExtractor, $propertyExtractor);
        $result = $normalizer->normalize($person, 'default');

        $expectedResult = array(
            'id' => 123,
            'name_value' => 'Bowl',
            'surName' => 'Of Soup',
            'initials' => null,
            'dateOfBirth' => '1980-01-01',
            'dateOfRegistration' => 'Apr. 2015',
            'addresses' => array(
                array(
                    'street' => 'Dummy Street',
                    'number' => null,
                    'postalCode' => null,
                    'city' => 'The City Is: Amsterdam',
                ),
                array(
                    'street' => null,
                    'number' => 4,
                    'postalCode' => '1234AB',
                    'city' => 'The City Is: ',
                ),
            ),
            'social' => array(
                'facebook' => 'Facebook ID',
                'twitter' => 'Twitter ID',
                'person' => array(
                    'id' => 123,
                ),
            ),
            'telephoneNumbers' => array(
                'home' => 123,
                'mobile' => 456,
                'work' => 789,
                'wife' => 777,
             ),
            'nonValidCollectionProperty' => null,
            'validCollectionPropertyWithCallback' => array(123),
            'validEmptyObjectProperty' => null,
        );

        $this->assertArraySubset($result, $expectedResult);
    }

    /**
     * @testdox Normalize object, no annotations on object.
     */
    public function testNormalizeNoAnnotations()
    {
        $classExtractor = new ClassExtractor(new AnnotationReader());
        $propertyExtractor = new PropertyExtractor(new AnnotationReader());

        $someClass = new SomeClass();

        $normalizer = new Normalizer($classExtractor, $propertyExtractor);
        $result = $normalizer->normalize($someClass);

        $this->assertSame(gettype(array()), gettype($result));
        $this->assertEmpty($result);
    }

    /**
     * @testdox Normalize object, no matching group.
     */
    public function testNormalizeNoGroupMatch()
    {
        $classExtractor = new ClassExtractor(new AnnotationReader());
        $propertyExtractor = new PropertyExtractor(new AnnotationReader());

        $normalizer = new Normalizer($classExtractor, $propertyExtractor);
        $result = $normalizer->normalize(new Person(), 'SomeUnknownGroup');

        $this->assertSame(gettype(array()), gettype($result));
        $this->assertEmpty($result);
    }

    /**
     * @testdox Normalize object, Circular reference, no fallback (hack!).
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Circular reference on: BowlOfSoup\NormalizerBundle\Tests\assets\Person called from: BowlOfSoup\NormalizerBundle\Tests\assets\Social. If possible, prevent this by adding a getId() method to BowlOfSoup\NormalizerBundle\Tests\assets\Person
     */
    public function testNormalizeCircularReferenceNoFallback()
    {
        $classExtractor = new ClassExtractor(new AnnotationReader());

        $stubPropertyExtractor = $this
            ->getMockBuilder('BowlOfSoup\NormalizerBundle\Service\PropertyExtractor')
            ->setConstructorArgs(array(new AnnotationReader()))
            ->setMethods(array('getId'))
            ->getMock();
        $stubPropertyExtractor
            ->expects($this->any())
            ->method('getId')
        ->will($this->returnValue(null));

        $person = $this->getDummyDataSet();

        $normalizer = new Normalizer($classExtractor, $stubPropertyExtractor);
        $normalizer->normalize($person, 'default');
    }

    /**
     * @return Person
     */
    private function getDummyDataSet()
    {
        $person = new Person();
        $person
            ->setId(123)
            ->setName('Bowl')
            ->setSurName('Of Soup')
            ->setDateOfBirth(new DateTime('1980-01-01'))
            ->setValidCollectionPropertyWithCallback(array(new SomeClass()));

        $social = new Social();
        $social
            ->setFacebook('Facebook ID')
            ->setTwitter('Twitter ID')
            ->setPerson($person);
        $person->setSocial($social);

        $addressCollection = new ArrayCollection();
        $address1 = new Address();
        $address1
            ->setStreet('Dummy Street')
            ->setCity('Amsterdam');
        $addressCollection->add($address1);
        $address2 = new Address();
        $address2
            ->setPostalCode('1234AB')
            ->setNumber(4);
        $addressCollection->add($address2);
        $person->setAddresses($addressCollection);

        $telephoneNumbers = new TelephoneNumbers();
        $telephoneNumbers
            ->setHome(123)
            ->setMobile(456)
            ->setWork(789)
            ->setWife(777);
        $person->setTelephoneNumbers($telephoneNumbers);

        return $person;
    }
}
