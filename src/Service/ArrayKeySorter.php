<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

class ArrayKeySorter
{
    public static function sortKeysAscRecursive(array &$data)
    {
        if (array_keys($data) !== range(0, count($data) - 1)) {
            ksort($data);
        }

        foreach ($data as &$subData) {
            if (is_array($subData)) {
                static::sortKeysAscRecursive($subData);
            }
        }
    }
}
