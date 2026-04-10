<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\EventListener;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

readonly class RegisterAnnotationsListener implements EventSubscriberInterface
{
    public function __construct(
        private ?bool $parameterRegisterAnnotations = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => [
                ['registerAnnotations', 0],
            ],
        ];
    }

    public function registerAnnotations(KernelEvent $event): void
    {
        if ($this->parameterRegisterAnnotations) {
            AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Normalize.php');
            AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Serialize.php');
            AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Translate.php');
            AnnotationRegistry::registerAutoloadNamespace('BowlOfSoup\NormalizerBundle\Annotation', __DIR__);
        }
    }
}
