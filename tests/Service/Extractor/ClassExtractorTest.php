<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\Service;

use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Tests\assets\SomeClass;
use PHPUnit\Framework\TestCase;

class ClassExtractorTest extends TestCase
{
    /**
     * @testdox Get a value for a property by specifying method, no method available.
     */
    public function testGetId(): void
    {
        $classExtractor = new ClassExtractor();

        $someClass = new SomeClass();
        $result = $classExtractor->getId($someClass);

        $this->assertSame(777, $result);
    }
}
