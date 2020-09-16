<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

class UnknownPropertyTranslate
{
    /**
     * @var string
     *
     * @Bos\Normalize(group={"default"})
     * @Bos\Translate(groupp={"default"})
     */
    private $name = 'foo';
}
