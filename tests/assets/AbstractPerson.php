<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use BowlOfSoup\NormalizerBundle\Annotation as Bos;

abstract class AbstractPerson
{
    /**
     * @Bos\Normalize(group={"parent_test"})
     *
     * @var string
     */
    private $name = 'parent-foo';

    /**
     * @Bos\Normalize(type="DateTime", group={"parent_test"})
     *
     * @throws \Exception
     *
     * @return \DateTime
     */
    public function getDateOfBirth()
    {
        return new \DateTime('1970-01-01');
    }
}
