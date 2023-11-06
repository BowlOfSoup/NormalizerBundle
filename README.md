Bowl Of Soup Normalizer
=====

[![Build Status](https://travis-ci.org/BowlOfSoup/NormalizerBundle.svg?branch=4.4)](https://travis-ci.org/BowlOfSoup/NormalizerBundle)
[![Coverage Status](https://coveralls.io/repos/github/BowlOfSoup/NormalizerBundle/badge.svg?branch=4.4)](https://coveralls.io/github/BowlOfSoup/NormalizerBundle?branch=4.4)
[![PHP Version](https://img.shields.io/badge/php-7.2.x%20--%208.2.x-blue.svg)](https://www.php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-4.4.x%20--%205.1.x-blue.svg)](https://symfony.com/)


Installation
-----
    composer require bowlofsoup/normalizer-bundle

Add the bundle to your `config/bundles.php` file

    BowlOfSoup\NormalizerBundle\BowlOfSoupNormalizerBundle::class => ['all' => true],

Quick feature overview
-----
- It's a Symfony bundle!
- Normalizes class properties and methods (public, protected, private)
- Can Serialize normalized content
- Works with Symfony and Doctrine as its ORM. Can handle Doctrine proxies
- Circular reference check: Handles circular reference by detecting it and returning content of the objects getId() method
- Object caching: If a getId() method is implemented for an object it will cache the normalized object per normalize command
- Annotation caching, this means speed!
    - The annotations for an object are cached. This means not parsing annotations multiple times for the same object. per flow (per normalize command)
    - In Symfony prod mode, annotations are cached completely (after first run)
- Symfony translations
    - Indicate domain (translation filename) and locale in annotations
    - Does not support formatting with ICU MessageFormat (yet), so no parameters

The main features are described in the [documentation](https://github.com/BowlOfSoup/NormalizerBundle/wiki).

Documentation
-----
Documentation on the usage and all supported options can be found [in the wiki](https://github.com/BowlOfSoup/NormalizerBundle/wiki).

1. [What is serialization and normalization?](https://github.com/BowlOfSoup/NormalizerBundle/wiki/What-is-serialization-and-normalization%3F)
2. [Installation](https://github.com/BowlOfSoup/NormalizerBundle/wiki/Installation)
3. [Serializing](https://github.com/BowlOfSoup/NormalizerBundle/wiki/Serializing)
    1. [Serialize annotations](https://github.com/BowlOfSoup/NormalizerBundle/wiki/Serialize-annotations)
4. [Normalizing](https://github.com/BowlOfSoup/NormalizerBundle/wiki/Normalizing)
    1. [Normalize annotations](https://github.com/BowlOfSoup/NormalizerBundle/wiki/Normalize-annotations)
5. [Translate a value](https://github.com/BowlOfSoup/NormalizerBundle/wiki/Translate-a-value)
    1. [Translate annotations](https://github.com/BowlOfSoup/NormalizerBundle/wiki/Translate-annotations)

Why use this normalizer and not ...
-----
- The Bowl Of Soup Normalizer uses an opt-in mechanism by default. You have to indicate which properties must be normalized
- You can indicate a context group, how is the value to be normalized, in which context?
- It's designed with speed in mind. Not packed with features for which you don't use half of it
- It has proven itself in a complex application with 15.000+ daily end users

