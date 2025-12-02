<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use Attribute;

/**
 * This is a custom broken annotation that will cause an Error when instantiated
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class BrokenAnnotation
{
    /**
     * Constructor with strict type that will cause TypeError
     */
    public function __construct(
        private readonly int $requiredInt,
    ) {
    }
}

class BrokenAttributeProperty
{
    /**
     * This attribute will cause TypeError when newInstance() is called
     * because we're passing a string where int is required
     */
    #[BrokenAnnotation('not_an_int')]
    private ?string $brokenProperty = null;

    public function getBrokenProperty(): ?string
    {
        return $this->brokenProperty;
    }
}
