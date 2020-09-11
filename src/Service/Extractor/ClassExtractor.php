<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Service\ObjectHelper;

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
        return ObjectHelper::getObjectId($object);
    }
}
