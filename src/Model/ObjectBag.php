<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Model;

readonly class ObjectBag
{
    private string $objectIdentifier;

    public function __construct(
        private object $object,
        mixed $objectIdentifier,
        private string $objectName,
    ) {
        $this->objectIdentifier = (string) $objectIdentifier;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getObjectIdentifier(): string
    {
        return $this->objectName . $this->objectIdentifier;
    }

    public function getObjectName(): string
    {
        return $this->objectName;
    }
}
