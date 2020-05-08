<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests;

use Symfony\Contracts\Translation\TranslatorInterface;

class DummyTranslator implements TranslatorInterface
{
    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $id;
    }
}
