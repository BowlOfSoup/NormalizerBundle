[![Build Status](https://travis-ci.org/BowlOfSoup/normalizer-bundle.svg?branch=master)](https://travis-ci.org/BowlOfSoup/normalizer-bundle)
[![Coverage Status](https://coveralls.io/repos/github/BowlOfSoup/NormalizerBundle/badge.svg?branch=master)](https://coveralls.io/github/BowlOfSoup/NormalizerBundle?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/eeab202d-8818-4de4-a66b-f49559d5399b/mini.png)](https://insight.sensiolabs.com/projects/eeab202d-8818-4de4-a66b-f49559d5399b)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%205.3-blue.svg)](https://php.net/)
[![Minimum Symfony Version](https://img.shields.io/badge/symfony-%3E%3D%202.7-green.svg)](https://symfony.com/)

Installation
------------
    composer require bowlofsoup/normalizer-bundle 1.*

Bowl Of Soup Normalizer
=======================

Annotations in your model
-------------------------
The normalizer uses annotations to indicate how the data should be represented. You can use the following annotations properties:

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
Sometimes you encouter an object for which you still want to use the legacy toArray() method, or just a custom method to normalize data for a property to normalize.
if used together with type="object", the callback is the method that is bound to the class property the annotation is set on, if used without type="", the callback relates to a method within the current class.

    /**
     * @Bos\Normalize(type="object", callback="toListArray")
     * @Bos\Normalize(callback="getPropertyToBeNormalized")
     */
    private $propertyToBeNormalized
 
### Normalize collections
If you have property which contains collection of other entities, you can use the type 'collection'. If you specify a callback, it will be applied to each item of the collection and placed to the result array.

_See paragraph Type_

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
The normalizer needs to be injected.

    <argument type="service" id="bos.normalizer" />
        
Calling the normalizer with a group is optional, but certainly recommended. The result will be an array.

    $result = $this->normalizer->normalize($someEntity, 'somegroup');
    $result = $this->normalizer->normalize(array($someEntity, $anotherEntity), 'somegroup');
