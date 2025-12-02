<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Extractor;

use BowlOfSoup\NormalizerBundle\Service\ObjectHelper;

class ClassExtractor
{
    public const string TYPE = 'class';

    /**
     * Gets the id from an object if available through getter.
     */
    public function getId(object $object): string|int|null
    {
        return ObjectHelper::getObjectId($object);
    }
}
