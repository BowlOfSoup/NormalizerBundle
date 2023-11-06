<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\EventListener;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class RegisterAnnotationsListener implements EventSubscriberInterface
{
    private ?bool $parameterRegisterAnnotations = null;

    /**
     * @param bool $parameterRegisterAnnotations
     */
    public function __construct(?bool $parameterRegisterAnnotations = null)
    {
        $this->parameterRegisterAnnotations = $parameterRegisterAnnotations;
    }

    /**
     * @inheritdoc
     */
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
