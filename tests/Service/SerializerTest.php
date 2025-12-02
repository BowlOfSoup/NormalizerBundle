<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderFactory;
use BowlOfSoup\NormalizerBundle\Service\Encoder\EncoderJson;
use BowlOfSoup\NormalizerBundle\Service\Serializer;
use BowlOfSoup\NormalizerBundle\Tests\assets\AddressWithAttributes;
use BowlOfSoup\NormalizerBundle\Tests\assets\MixedAnnotations;
use BowlOfSoup\NormalizerBundle\Tests\assets\OrderWithAttributes;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use BowlOfSoup\NormalizerBundle\Tests\assets\PersonWithAttributes;
use BowlOfSoup\NormalizerBundle\Tests\assets\ProductWithAttributes;
use BowlOfSoup\NormalizerBundle\Tests\assets\Social;
use BowlOfSoup\NormalizerBundle\Tests\SerializerTestTrait;
use PHPUnit\Framework\TestCase;

class SerializerTest extends TestCase
{
    use SerializerTestTrait;

    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->getSerializer();
    }

    /**
     * @testdox Serialize array, with class annotation.
     */
    public function testSerializeSuccess(): void
    {
        $this->assertSame(
            '{"wrapperElement":{"addresses":null,"dateOfBirth":null,"dateOfRegistration":"Apr. 2015","hobbies":null,"id":123,"initials":null,"name_value":"Bowl Of Soup","nonValidCollectionProperty":null,"social":{"twitter":"@bos"},"surName":null,"telephoneNumbers":null,"testForNormalizingCallback":[{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},{"city":"The City Is: ","getSpecialNotesForDelivery":"some special string","number":4,"postalCode":"1234AB","street":null}],"testForNormalizingCallbackArray":["123","456","789"],"testForNormalizingCallbackObject":{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},"testForNormalizingCallbackString":"asdasd","testForProxy":null,"validCollectionPropertyWithCallback":null,"validEmptyObjectProperty":null}}',
            $this->serializer->serialize($this->getPersonObject(), EncoderFactory::TYPE_JSON, 'default')
        );
    }

    /**
     * @testdox Serialize array, with class annotation, wrong group.
     */
    public function testSerializeSuccessUnknownGroupForClassAnnotation(): void
    {
        $this->assertSame(
            '{"addresses":null}',
            $this->serializer->serialize($this->getPersonObject(), EncoderFactory::TYPE_JSON, 'maxDepthTestDepthNoIdentifier')
        );
    }

    /**
     * Serialize array, with class annotation and custom encoder settings.
     */
    public function testSerializeWithCustomEncoder(): void
    {
        $encoderJson = new EncoderJson();
        $encoderJson->setOptions(JSON_FORCE_OBJECT);

        $this->assertSame(
            '{"wrapperElement":{"addresses":null,"dateOfBirth":null,"dateOfRegistration":"Apr. 2015","hobbies":null,"id":123,"initials":null,"name_value":"Bowl Of Soup","nonValidCollectionProperty":null,"social":{"twitter":"@bos"},"surName":null,"telephoneNumbers":null,"testForNormalizingCallback":{"0":{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},"1":{"city":"The City Is: ","getSpecialNotesForDelivery":"some special string","number":4,"postalCode":"1234AB","street":null}},"testForNormalizingCallbackArray":{"0":"123","1":"456","2":"789"},"testForNormalizingCallbackObject":{"city":"The City Is: Amsterdam","getSpecialNotesForDelivery":"some special string","number":null,"postalCode":null,"street":"Dummy Street"},"testForNormalizingCallbackString":"asdasd","testForProxy":null,"validCollectionPropertyWithCallback":null,"validEmptyObjectProperty":null}}',
            $this->serializer->serialize($this->getPersonObject(), $encoderJson, 'default')
        );
    }

    /**
     * @testdox Serialize array, no class annotation.
     */
    public function testSerializeNoClassAnnotation(): void
    {
        $social = new Social();
        $social->setTwitter('@bos');

        $this->assertSame(
            '<?xmlversion="1.0"encoding="UTF-8"?><data><twitter>@bos</twitter></data>',
            trim((string) preg_replace('/\s+/', '', $this->serializer->serialize($social, EncoderFactory::TYPE_XML, 'default')))
        );
    }

    /**
     * @testdox Serialize object with PHP 8 attributes instead of docblock annotations.
     */
    public function testSerializeWithPhp8Attributes(): void
    {
        $person = new PersonWithAttributes();
        $person->setId(999);
        $person->setName('John Doe');
        $person->setEmail('john@example.com');
        $person->setBirthDate(new \DateTime('1990-05-15'));

        $address = new AddressWithAttributes();
        $address->setId(1);
        $address->setStreet('Main Street');
        $address->setNumber(123);
        $address->setCity('Amsterdam');
        $address->setPostalCode('1234AB');
        $person->setPrimaryAddress($address);

        $result = $this->serializer->serialize($person, EncoderFactory::TYPE_JSON, 'default');
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('data', $decoded);
        $this->assertSame(999, $decoded['data']['id']);
        $this->assertSame('John Doe', $decoded['data']['full_name']);
        $this->assertSame('john@example.com', $decoded['data']['email']);
        $this->assertSame('1990-05-15', $decoded['data']['birthDate']);
        $this->assertSame('1990', $decoded['data']['birthYear']);
        $this->assertSame('john@example.com', $decoded['data']['contactEmail']);
        $this->assertArrayHasKey('primaryAddress', $decoded['data']);
        $this->assertSame('Main Street', $decoded['data']['primaryAddress']['street']);
        $this->assertSame(123, $decoded['data']['primaryAddress']['number']);
        $this->assertSame('Amsterdam', $decoded['data']['primaryAddress']['city']);
    }

    /**
     * @testdox Serialize object with mixed docblock and PHP 8 attribute annotations.
     */
    public function testSerializeWithMixedAnnotations(): void
    {
        $mixed = new MixedAnnotations();
        $mixed->setIdFromDocblock(100);
        $mixed->setNameFromAttribute('Mixed Test');
        $mixed->setDualAnnotated('Dual Value');

        $resultDocblock = $this->serializer->serialize($mixed, EncoderFactory::TYPE_JSON, 'docblock');
        $resultAttribute = $this->serializer->serialize($mixed, EncoderFactory::TYPE_JSON, 'attribute');
        $resultDefault = $this->serializer->serialize($mixed, EncoderFactory::TYPE_JSON, 'default');

        $decodedDocblock = json_decode($resultDocblock, true);
        $decodedAttribute = json_decode($resultAttribute, true);
        $decodedDefault = json_decode($resultDefault, true);

        $this->assertArrayHasKey('mixed', $decodedDocblock);
        $this->assertArrayHasKey('idFromDocblock', $decodedDocblock['mixed']);
        $this->assertSame(100, $decodedDocblock['mixed']['idFromDocblock']);

        $this->assertArrayHasKey('attributes', $decodedAttribute);
        $this->assertArrayHasKey('nameFromAttribute', $decodedAttribute['attributes']);
        $this->assertSame('Mixed Test', $decodedAttribute['attributes']['nameFromAttribute']);

        $this->assertArrayHasKey('nameFromAttribute', $decodedDefault);
        $this->assertSame('Mixed Test', $decodedDefault['nameFromAttribute']);
    }

    /**
     * @testdox Serialize with PHP 8 attributes using different groups (api vs default).
     */
    public function testSerializeAttributesWithGroups(): void
    {
        $person = new PersonWithAttributes();
        $person->setId(555);
        $person->setName('Jane Doe');
        $person->setEmail('jane@example.com');

        $resultApi = $this->serializer->serialize($person, EncoderFactory::TYPE_JSON, 'api');
        $decodedApi = json_decode($resultApi, true);

        $this->assertArrayHasKey('id', $decodedApi);
        $this->assertArrayHasKey('email', $decodedApi);
        $this->assertArrayNotHasKey('full_name', $decodedApi);
    }

    /**
     * @testdox Serialize with PHP 8 attributes using translation annotations.
     */
    public function testSerializeAttributesWithTranslation(): void
    {
        $product = new ProductWithAttributes();
        $product->setId(100);
        $product->setName('laptop');
        $product->setDescription('gaming.laptop');
        $product->setPrice(1299.99);
        $product->setCost(800.00);
        $product->setCreatedAt(new \DateTime('2024-01-15'));

        $result = $this->serializer->serialize($product, EncoderFactory::TYPE_JSON, 'api');
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('product', $decoded);
        $this->assertSame(100, $decoded['product']['id']);
        $this->assertSame('translatedValue', $decoded['product']['product_name']);
        $this->assertSame('translatedValue', $decoded['product']['description']);
        $this->assertSame('2024-01-15', $decoded['product']['createdAt']);
        $this->assertSame(1299.99, $decoded['product']['price']);
        $this->assertSame('$1299.99', $decoded['product']['formatted_price']);
        $this->assertArrayNotHasKey('cost', $decoded['product']);
    }

    /**
     * @testdox Serialize with PHP 8 attributes testing different groups (api vs internal).
     */
    public function testSerializeAttributesWithDifferentGroups(): void
    {
        $product = new ProductWithAttributes();
        $product->setId(200);
        $product->setName('keyboard');
        $product->setPrice(99.99);
        $product->setCost(45.00);

        $resultApi = $this->serializer->serialize($product, EncoderFactory::TYPE_JSON, 'api');
        $decodedApi = json_decode($resultApi, true);

        $resultInternal = $this->serializer->serialize($product, EncoderFactory::TYPE_JSON, 'internal');
        $decodedInternal = json_decode($resultInternal, true);

        // API group should not include cost or profit_margin
        $this->assertArrayHasKey('product', $decodedApi);
        $this->assertArrayNotHasKey('cost', $decodedApi['product']);
        $this->assertArrayNotHasKey('profit_margin', $decodedApi['product']);

        // Internal group should include cost and profit_margin
        $this->assertArrayHasKey('product', $decodedInternal);
        $this->assertEqualsWithDelta(45.00, $decodedInternal['product']['cost'], 0.01);
        $this->assertEqualsWithDelta(122.2, $decodedInternal['product']['profit_margin'], 0.1);
        $this->assertArrayNotHasKey('formatted_price', $decodedInternal['product']);
    }

    /**
     * @testdox Serialize complex object with PHP 8 attributes including collections and nested objects.
     */
    public function testSerializeAttributesWithCollectionsAndNestedObjects(): void
    {
        $order = new OrderWithAttributes();
        $order->setId(1000);
        $order->setOrderNumber('ORD-2024-001');
        $order->setOrderDate(new \DateTime('2024-01-01T10:00:00'));
        $order->setEmail('customer@example.com');

        $product1 = new ProductWithAttributes();
        $product1->setId(1);
        $product1->setName('Mouse');
        $product1->setPrice(29.99);

        $product2 = new ProductWithAttributes();
        $product2->setId(2);
        $product2->setName('Keyboard');
        $product2->setPrice(79.99);

        $order->addItem($product1);
        $order->addItem($product2);

        $address = new AddressWithAttributes();
        $address->setId(500);
        $address->setStreet('Main St');
        $address->setNumber(123);
        $address->setCity('Amsterdam');
        $address->setPostalCode('1000AA');
        $order->setShippingAddress($address);

        $result = $this->serializer->serialize($order, EncoderFactory::TYPE_JSON, 'api');
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('order', $decoded);
        $this->assertSame(1000, $decoded['order']['id']);
        $this->assertSame('ORD-2024-001', $decoded['order']['order_number']);
        $this->assertSame('customer@example.com', $decoded['order']['customer_email']);
        $this->assertSame(2, $decoded['order']['total_items']);
        $this->assertIsString($decoded['order']['order_status']);
        $this->assertIsArray($decoded['order']['items']);
        $this->assertCount(2, $decoded['order']['items']);
        $this->assertArrayHasKey('shippingAddress', $decoded['order']);
        $this->assertIsArray($decoded['order']['shippingAddress']);
        $this->assertSame('Amsterdam', $decoded['order']['shippingAddress']['city']);
    }

    /**
     * @testdox Serialize with PHP 8 attributes using skipEmpty functionality.
     */
    public function testSerializeAttributesWithSkipEmpty(): void
    {
        $order1 = new OrderWithAttributes();
        $order1->setId(2000);
        $order1->setOrderNumber('ORD-2024-002');
        $order1->setOrderDate(new \DateTime('2024-01-01'));
        // email and internalNotes are null

        $result1 = $this->serializer->serialize($order1, EncoderFactory::TYPE_JSON, 'admin');
        $decoded1 = json_decode($result1, true);

        // Empty fields with skipEmpty should not be present
        $this->assertArrayNotHasKey('customer_email', $decoded1['order']);
        $this->assertArrayNotHasKey('internalNotes', $decoded1['order']);

        $order2 = new OrderWithAttributes();
        $order2->setId(3000);
        $order2->setOrderNumber('ORD-2024-003');
        $order2->setOrderDate(new \DateTime('2024-01-01'));
        $order2->setEmail('test@example.com');
        $order2->setInternalNotes('Priority shipping');

        $result2 = $this->serializer->serialize($order2, EncoderFactory::TYPE_JSON, 'admin');
        $decoded2 = json_decode($result2, true);

        // Non-empty fields should be present
        $this->assertArrayHasKey('customer_email', $decoded2['order']);
        $this->assertSame('test@example.com', $decoded2['order']['customer_email']);
        $this->assertArrayHasKey('internalNotes', $decoded2['order']);
        $this->assertSame('Priority shipping', $decoded2['order']['internalNotes']);
    }

    /**
     * @testdox Serialize with PHP 8 attributes testing method normalization with computed values.
     */
    public function testSerializeAttributesWithComputedMethodValues(): void
    {
        $product = new ProductWithAttributes();
        $product->setId(300);
        $product->setName('monitor');
        $product->setPrice(399.99);
        $product->setCost(250.00);

        $result = $this->serializer->serialize($product, EncoderFactory::TYPE_JSON, 'internal');
        $decoded = json_decode($result, true);

        $this->assertArrayHasKey('product', $decoded);
        $this->assertArrayHasKey('profit_margin', $decoded['product']);
        $this->assertEqualsWithDelta(60.0, $decoded['product']['profit_margin'], 0.1);
    }

    private function getPersonObject(): Person
    {
        $person = new Person();
        $person->setId(123);
        $person->setName('Bowl Of Soup');

        $social = new Social();
        $social->setId(456);
        $social->setTwitter('@bos');

        $person->setSocial($social);

        return $person;
    }
}
