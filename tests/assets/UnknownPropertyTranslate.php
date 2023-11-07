<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class UnknownPropertyTranslate
{
    /**
     * @Bos\Normalize(group={"default"})
     *
     * @Bos\Translate(groupp={"default"})
     *
     * @var string
     */
    private $name = 'foo';
}
