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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Contracts\Translation\TranslatorInterface;

trait NormalizerTestTrait
{
    protected ClassExtractor|Stub $classExtractor;
    protected PropertyNormalizer|Stub $propertyNormalizer;
    protected MethodNormalizer|Stub $methodNormalizer;
    protected PropertyExtractor|Stub $propertyExtractor;
    protected MethodExtractor|Stub $methodExtractor;
    protected AnnotationExtractor|Stub $annotationExtractor;
    protected TranslatorInterface|MockObject $translator;

    public function getNormalizer(): Normalizer
    {
        $propertyExtractor = $this->propertyExtractor ?? new PropertyExtractor();
        $methodExtractor = $this->methodExtractor ?? new MethodExtractor();
        $classExtractor = $this->classExtractor ?? new ClassExtractor();
        $annotationExtractor = $this->annotationExtractor ?? new AnnotationExtractor();

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
