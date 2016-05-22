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
     * @param KernelEvent $event
     */
    public function registerAnnotations(KernelEvent $event)
    {
        if ($this->parameterRegisterAnnotations) {
            AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Normalize.php');
            AnnotationRegistry::registerAutoloadNamespace('BowlOfSoup\NormalizerBundle\Annotation', __DIR__);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            'kernel.request' => array(
                array('registerAnnotations', 0),
            ),
        );
    }
}
