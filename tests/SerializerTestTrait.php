<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests;

use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;
use BowlOfSoup\NormalizerBundle\Service\Serializer;

trait SerializerTestTrait
{
    use NormalizerTestTrait;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $annotationExtractor;

    public function getSerializer(): Serializer
    {
        $annotationExtractor = $this->annotationExtractor ?? new AnnotationExtractor();

        $normalizer = $this->getNormalizer();

        return new Serializer($annotationExtractor, $normalizer);
    }
}
