<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests;

use BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\MethodExtractor;
use BowlOfSoup\NormalizerBundle\Service\Extractor\PropertyExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalize\MethodNormalizer;
use BowlOfSoup\NormalizerBundle\Service\Normalize\PropertyNormalizer;
use BowlOfSoup\NormalizerBundle\Service\Normalizer;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\AnnotationExtractor|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $annotationExtractor;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\Stub\Stub */
    protected $translator;

    public function getNormalizer(): Normalizer
    {
        $propertyExtractor = $this->propertyExtractor ?? new PropertyExtractor();
        $methodExtractor = $this->methodExtractor ?? new MethodExtractor();
        $classExtractor = $this->classExtractor ?? new ClassExtractor();

        $annotationExtractor = $this->annotationExtractor ?? new AnnotationExtractor();

        /** @var \PHPUnit\Framework\MockObject\MockBuilder $translationMockBuilder */
        $translationMockBuilder = $this->getMockBuilder(TranslatorInterface::class);
        $translationMockBuilder->disableOriginalConstructor();

        $this->translator = $translationMockBuilder
            ->onlyMethods(['trans'])
            ->getMock();
        $this->translator
            ->method('trans')
            ->willReturn('translatedValue');

        $propertyNormalizer = $this->propertyNormalizer ?? new PropertyNormalizer($classExtractor, $this->translator, $annotationExtractor, $propertyExtractor);
        $methodNormalizer = $this->methodNormalizer ?? new MethodNormalizer($classExtractor, $this->translator, $annotationExtractor, $methodExtractor);

        return new Normalizer($classExtractor, $propertyNormalizer, $methodNormalizer);
    }
}
