<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests;

use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalize\MethodNormalizer;
use BowlOfSoup\NormalizerBundle\Service\Normalize\PropertyNormalizer;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use Doctrine\Common\Annotations\AnnotationReader;

trait NormalizerTestTrait
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $classExtractor;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalize\PropertyNormalizer|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $propertyNormalizer;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalize\MethodNormalizer|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $methodNormalizer;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $propertyExtractor;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $methodExtractor;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $translator;

    public function getNormalizer(): Normalizer
    {
        $propertyExtractor = $this->propertyExtractor ?? new PropertyExtractor(new AnnotationReader());
        $methodExtractor = $this->methodExtractor ?? new MethodExtractor(new AnnotationReader());
        $classExtractor = $this->classExtractor ?? new ClassExtractor(new AnnotationReader());
        $this->translator = $this->translator ?? new DummyTranslator();

        $propertyNormalizer = $this->propertyNormalizer ?? new PropertyNormalizer($classExtractor, $this->translator, $propertyExtractor);
        $methodNormalizer = $this->methodNormalizer ?? new MethodNormalizer($classExtractor, $this->translator, $methodExtractor);

        return new Normalizer($classExtractor, $propertyNormalizer, $methodNormalizer);
    }
}
