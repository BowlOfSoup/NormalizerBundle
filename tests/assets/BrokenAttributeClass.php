<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use Attribute;

/**
 * This is a custom broken annotation for classes that will cause an Error when instantiated
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class BrokenClassAnnotation
{
    /**
     * Constructor with strict type that will cause TypeError
     */
    public function __construct(
        private readonly float $requiredFloat,
    ) {
    }
}

/**
 * This attribute will cause TypeError when newInstance() is called
 */
#[BrokenClassAnnotation('not_a_float')]
class BrokenAttributeClass
{
    private ?string $field = null;
}
