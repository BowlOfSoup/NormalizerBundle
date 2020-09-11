<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

class ObjectHelper
{
    public static function hashObject($object, $algorithm = 'md5'): string
    {
        $serializedObject = serialize($object);

        return hash($algorithm, $serializedObject);
    }
}
