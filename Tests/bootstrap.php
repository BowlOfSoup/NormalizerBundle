<?php

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if (!includeIfExists(__DIR__ . '/../../../../vendor/autoload.php') &&
    !includeIfExists(__DIR__ . '/../../vendor/autoload.php') &&
    !includeIfExists(__DIR__ . '/../vendor/autoload.php')
) {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}

if (class_exists('Doctrine\Common\Annotations\AnnotationRegistry')) {
    \Doctrine\Common\Annotations\AnnotationRegistry::registerFile(__DIR__ . '/../Annotation/Normalize.php');
    \Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace('BowlOfSoup\NormalizerBundle\Annotation', __DIR__);
}
