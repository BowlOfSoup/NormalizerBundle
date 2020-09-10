<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

class ClassExtractor
{
    /** @var string */
    public const TYPE = 'class';

    /**
     * Gets the id from an object if available through getter.
     *
     * @return string|int|null
     */
    public function getId(object $object)
    {
        $method = 'getId';
        if (is_callable([$object, 'getId'])) {
            return $object->$method();
        }

        return null;
    }
}
