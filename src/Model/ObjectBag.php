<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Model;

class ObjectBag
{
    private object $object;
    private string $objectIdentifier;
    private string $objectName;

    /**
     * @param mixed $objectIdentifier
     */
    public function __construct(
        object $object,
        $objectIdentifier,
        string $objectName
    ) {
        $this->object = $object;
        $this->objectIdentifier = (string) $objectIdentifier;
        $this->objectName = $objectName;
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
