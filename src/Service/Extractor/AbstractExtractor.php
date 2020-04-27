<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

use Doctrine\Common\Annotations\Reader;

abstract class AbstractExtractor
{
    /** @var bool */
    public const GET_ONLY_PRIVATES = true;

    /** @var \Doctrine\Common\Annotations\Reader */
    protected $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Gets the id from an object if available through getter.
     *
     * @return string|int|null
     */
    public function getId(object $object)
    {
        return $this->getValueByMethod($object, 'getId');
    }

    /**
     * Returns a value by specified method.
     *
     * @return mixed
     */
    public function getValueByMethod(object $object, string $method)
    {
        if (is_callable([$object, $method])) {
            return $object->$method();
        }

        return null;
    }
}
