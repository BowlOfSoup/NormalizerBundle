[![Build Status](https://travis-ci.org/BowlOfSoup/NormalizerBundle.svg?branch=master)](https://travis-ci.org/BowlOfSoup/NormalizerBundle)
[![Coverage Status](https://coveralls.io/repos/github/BowlOfSoup/NormalizerBundle/badge.svg?branch=master)](https://coveralls.io/github/BowlOfSoup/NormalizerBundle?branch=master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-blue.svg?no-cache=1)](https://php.net/)
[![Minimum Symfony Version](https://img.shields.io/badge/symfony-%3E%3D%204.4-green.svg)](https://symfony.com/)

Installation
------------
    composer require bowlofsoup/normalizer-bundle

Add the bundle to your `config/bundles.php` file

    BowlOfSoup\NormalizerBundle\BowlOfSoupNormalizerBundle::class => ['all' => true],

Bowl Of Soup Normalizer
=======================

- Normalizes properties and methods (public, protected, private)
- Serialized normalized content
- Works with Symfony and Doctrine as its ORM. Can handle Doctrine proxies
- Circular reference check: Handles circular reference by detecting it and returning content of the objects getId() method
- Object caching: If a getId() method is implemented for an object it will cache the normalized object per normalize command
- Annotation caching, this means speed!
    - The annotations for an object are cached. This means not parsing annotations multiple times for the same object. per flow (per normalize command)
    - In Symfony prod mode, annotations are cached completely (after first run)
- Symfony translations
    - Indicate domain (translation filename) and locale in annotations
    - Does not support formatting with ICU MessageFormat (yet), so no parameters

The main features are described in the corresponding annotations.

What is serialization/normalization?
------------------------------------

![visual serialization/normalization](https://symfony.com/doc/current/_images/components/serializer/serializer_workflow.svg)
(Source: [Symfony Serializer](https://symfony.com/doc/current/components/serializer.html))

Serialization of an object is visualized on the right side of the above visual. It consists of two steps,
normalization and encoding normalized data.

You can call each step separately (normalize, encode) or directly serialize an object.

# Serializer

Annotations in your model
-------------------------
As we see in the visual the first step in serialization is normalizing. To indicate the way object properties and methods
need to be normalized the "Normalizer" annotations have to be used. See paragraph "Normalizer" for annotation usage.

For serialization two encodings are supported: **JSON** and **XML**.

### Use statement and alias
On top of the object you want to serialize:

    use BowlOfSoup\NormalizerBundle\Annotation as Bos;

### Wrapper element
When outputting to a specific encoding you can indicate the wrapping element, this element will be the root node.
You can and should indicate a group (context), use property 'group' to separate context.

    /**
     * @Bos\Serialize(wrapElement="SomeWrapperElement", group={"default"})
     */
    class ClassToBeSerialized
    {

### Calling the serializer
The serializer can be injected, but also auto-wired.

    <argument type="service" id="bos.serializer" />

You can input an object or an array. If you input an object, it will normalize first, and thus the "Normalize" annotations are used.

Calling the serializer with a group is optional, but certainly recommended.

    $result = $this->serializer->serialize($someEntity, 'somegroup');

The result will be a string of data.

# Normalizer

Annotations in your model
-------------------------
The normalizer uses annotations to indicate how the data should be represented. You can use the following annotations properties:

These properties can be used on class/object *properties* and *methods*.

### Use statement and alias
    BowlOfSoup\NormalizerBundle\Annotation as Bos;
 
### Include class property in normalization
Use the following annotation to include the class property in the normalization functionality.

    /** 
     * @Bos\Normalize
     */
    private $propertyToBeNormalized
 
### Maximal depth
To normalize until a certain depth (type="object" / type="collection"). Property can only be set on class level.

    /** 
     * @Bos\Normalize(maxDepth=2)
     */
    class ClassToBeNormalized
    {

### Skip empty properties
You can omit properties that are empty to be normalized. If the property contains no data (empty, or null) the normalized output will not contain this property.

    /** 
     * @Bos\Normalize(skipEmpty=true)
     */
    private $propertyToBeNormalized
 
This can also be used on class level. All properties which are empty, will now not be normalized.

    /** 
     * @Bos\Normalize(skipEmpty=true)
     */
    class ClassToBeNormalized
    {

### Group or context support

Use property 'group' to separate context.

    /**
     * @Bos\Normalize(group={"default"})
     * @Bos\Normalize(group={"customgroup", "customgroup2"}, name="something")
     */
    private $propertyToBeNormalized
 
### Name
Use property 'name' to change the key (name) of the class property to be normalized.

    /**
     * @Bos\Normalize(name="automobile")
     */
    private $propertyToBeNormalized

### Type
Indicate if the class property is a of 'special' type. Type can be 'collection', 'object', or 'datetime'.
If an object is empty, value 'null' will be returned.

    /**
     * @Bos\Normalize(type="DateTime")
     * @Bos\Normalize(type="object") 
     * @Bos\Normalize(type="collection", callback="toListArray")
     */
    private $propertyToBeNormalized
 
### Format
Format the 'DateTime' type. Can only be used for type="DateTime".

    /**
     * @Bos\Normalize(type="DateTime", format="Y-m-d")
     */
    private $propertyToBeNormalized
 
### Callback
Sometimes you encouter an object for which you still want to use a legacy method, or just a custom method to normalize data for a property to normalize.
if used together with type="object", the callback is the method that is bound to the class property the annotation is set on, if used without type="", the callback relates to a method within the current class.

    /**
     * @Bos\Normalize(type="object", callback="toListArray")
     * @Bos\Normalize(callback="getPropertyToBeNormalized")
     */
    private $propertyToBeNormalized
 
*Note: callbacks can't be used on methods, since a method can surely function as callback.*

### Normalize callback output
It is possible to normalize output from a callback method.
E.g. if you return an array with objects or just a single object from a callback method it will also normalize those objects.

    /**
     * @Bos\Normalize(callback="legacyMethod", normalizeCallbackResult=true)
     * @Bos\Normalize(type="object", callback="legacyMethod", normalizeCallbackResult=true)
     * @Bos\Normalize(type="collection", callback="legacyMethod", normalizeCallbackResult=true)
     */
    private $propertyToBeNormalized

*Note: callbacks can't be used on methods.* 

### Normalize collections
If you have property which contains collection of other entities, you can use the type 'collection'. If you specify a callback, it will be applied to each item of the collection and placed to the result array.

_See paragraph: Type_

### Usage in multiple context
As you can see, per group you can specify different outcomes.

    /**
     * @Bos\Normalize(group={"somegroup"}, name="automobile")
     * @Bos\Normalize(group={"someotherspecialgroup"}) 
     */
    private $propertyToBeNormalized

    /**
     * @Bos\Normalize(type="DateTime", format="Y-m-d", group={"somegroup"}, name="creationDate")
     * @Bos\Normalize(type="DateTime", format="Y", group={"someotherspecialgroup"}) 
     */
    private $propertyToBeNormalized
 
### Calling the normalizer
The normalizer needs to be injected, but can also be auto-wired.

    <argument type="service" id="bos.normalizer" />
        
Calling the normalizer with a group is optional, but certainly recommended. The result will be an *array*.

    $result = $this->normalizer->normalize($someEntity, 'somegroup');
    $result = $this->normalizer->normalize(array($someEntity, $anotherEntity), 'somegroup');

Using translations
------------------
### Use statement and alias
    BowlOfSoup\NormalizerBundle\Annotation as Bos;

### Add the annotation to your property/method

    /**
     * @Bos\Normalize(group={"somegroup"}, name="automobile")
     * @Bos\Translate(group={"translation"})
     */
    private $propertyToBeNormalized

This will try to translate the value in `$propertyToBeNormalized`.
By default, the translation must be in a `Resources/translations/messages.en.xliff`.

### Specify your domain (the .xliff file)

    /**
     * @Bos\Normalize(group={"somegroup"}, name="automobile")
     * @Bos\Translate(group={"translation"}, domain="some_domain")
     */
    private $propertyToBeNormalized

This tries to find the translation in `Resources/translations/some_domain.en.xliff`.

### Specify your locale (language)

You can specify your locale, if you did not set that globally in Symfony.

    /**
     * @Bos\Normalize(group={"somegroup"}, name="automobile")
     * @Bos\Translate(group={"translation"}, domain="some_domain", locale="nl")
     */
    private $propertyToBeNormalized

This tries to find the translation in `Resources/translations/some_domain.nl.xliff`.
Notice the `nl` in the file name (Dutch language).