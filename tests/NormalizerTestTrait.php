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

    /** @var \Symfony\Contracts\Translation\TranslatorInterface|\PHPUnit\Framework\MockObject\Stub\Stub|\PHPUnit\Framework\MockObject\MockObject */
    protected $translator;

    public function getNormalizer(): Normalizer
    {
        $propertyExtractor = $this->propertyExtractor ?? new PropertyExtractor();
        $methodExtractor = $this->methodExtractor ?? new MethodExtractor();
        $classExtractor = $this->classExtractor ?? new ClassExtractor();

        $annotationExtractor = $this->annotationExtractor ?? new AnnotationExtractor();

        /** @var \PHPUnit\Framework\MockObject\MockBuilder|\PHPUnit\Framework\MockObject\MockObject $translationMockBuilder */
        $translationMockBuilder = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor();

        $mockMethodsTranslator = ['trans'];
        if (method_exists(TranslatorInterface::class, 'getLocale')) { // Support for Symfony < 6
            $mockMethodsTranslator[] = 'getLocale';
        }

        $this->translator = $translationMockBuilder
            ->onlyMethods($mockMethodsTranslator)
            ->getMock();
        $this->translator
            ->method('trans')
            ->willReturn('translatedValue');

        if (method_exists(TranslatorInterface::class, 'getLocale')) { // Support for Symfony < 6
            $this->translator
                ->method('getLocale')
                ->willReturn('en');
        }

        $propertyNormalizer = $this->propertyNormalizer ?? new PropertyNormalizer($classExtractor, $this->translator, $annotationExtractor, $propertyExtractor);
        $methodNormalizer = $this->methodNormalizer ?? new MethodNormalizer($classExtractor, $this->translator, $annotationExtractor, $methodExtractor);

        return new Normalizer($classExtractor, $propertyNormalizer, $methodNormalizer);
    }
}
