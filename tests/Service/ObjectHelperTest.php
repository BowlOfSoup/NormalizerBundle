<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\ObjectHelper;
use BowlOfSoup\NormalizerBundle\Tests\assets\Person;
use PHPUnit\Framework\TestCase;

class ObjectHelperTest extends TestCase
{
    public function testObjectIdentifiedByGetIdMethod(): void
    {
        $person = (new Person())
            ->setId(123);

        $this->assertSame(123, ObjectHelper::getObjectIdentifier($person));
    }

    public function testObjectIdentifiedByHash(): void
    {
        $someClass = new \stdClass();

        $this->assertSame('f7827bf44040a444ac855cd67adfb502', ObjectHelper::getObjectIdentifier($someClass));
    }

    public function testNoObjectGiven(): void
    {
        $this->assertNull(ObjectHelper::getObjectIdentifier([]));
    }
}
