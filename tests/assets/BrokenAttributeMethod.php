<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests\assets;

use Attribute;

/**
 * This is a custom broken annotation for methods that will cause an Error when instantiated
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class BrokenMethodAnnotation
{
    /**
     * Constructor with strict type that will cause TypeError
     */
    public function __construct(
        private readonly bool $requiredBool,
    ) {
    }
}

class BrokenAttributeMethod
{
    /**
     * This attribute will cause TypeError when newInstance() is called
     */
    #[BrokenMethodAnnotation('not_a_bool')]
    public function getBrokenMethod(): string
    {
        return 'broken';
    }
}
