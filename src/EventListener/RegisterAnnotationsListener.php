<?php

namespace BowlOfSoup\NormalizerBundle\EventListener;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class RegisterAnnotationsListener implements EventSubscriberInterface
{
    /** @var bool */
    private $parameterRegisterAnnotations;

    /**
     * @param bool $parameterRegisterAnnotations
     */
    public function __construct($parameterRegisterAnnotations)
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
            AnnotationRegistry::registerAutoloadNamespace('BowlOfSoup\NormalizerBundle\Annotation', __DIR__);
        }
    }
}
