<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use BowlOfSoup\NormalizerBundle\Model\ObjectBag;
use BowlOfSoup\NormalizerBundle\Model\ObjectCache;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalize\MethodNormalizer;
use BowlOfSoup\NormalizerBundle\Service\Normalize\PropertyNormalizer;

class Normalizer
{
    public function __construct(
        protected ClassExtractor $classExtractor,
        private readonly PropertyNormalizer $propertyNormalizer,
        private readonly MethodNormalizer $methodNormalizer,
    ) {
    }

    /**
     * Normalize an object or an array of objects, for a specific group.
     *
     * @throws BosNormalizerException
     * @throws \ReflectionException
     */
    public function normalize(mixed $data, ?string $group = null): array
    {
        if (empty($data)) {
            return [];
        }

        $this->cleanUpSession();

        return $this->normalizeData($data, $group);
    }

    /**
     * Get properties for given object, annotations per property and begin normalizing.
     *
     * @throws BosNormalizerException
     * @throws \ReflectionException
     */
    public function normalizeObject(object $object, ?string $group): array
    {
        $normalizedConstructs = [];
        $objectName = $object::class;
        $objectIdentifier = ObjectHelper::getObjectIdentifier($object);

        ObjectCache::setObjectByName($objectName, $objectIdentifier);

        // If cached return previously cached normalized object.
        if (null !== $objectIdentifier && ObjectCache::hasObjectByNameAndIdentifier($objectName, $objectIdentifier)) {
            return ObjectCache::getObjectByNameAndIdentifier($objectName, $objectIdentifier);
        }
        ObjectCache::resetObjectByNameAndIdentifier($objectName, $objectIdentifier);

        $objectBag = new ObjectBag($object, $objectIdentifier, $objectName);

        $normalizedClassProperties = $this->propertyNormalizer->normalize($this, $objectBag, $group);
        $normalizedConstructs = array_merge($normalizedConstructs, ...$normalizedClassProperties);

        $normalizedClassMethods = $this->methodNormalizer->normalize($this, $objectBag, $group);
        $normalizedConstructs = array_merge($normalizedConstructs, ...$normalizedClassMethods);

        if (null !== $objectIdentifier) {
            ObjectCache::setNormalizedPropertiesByNameAndIdentifier($objectName, $objectIdentifier, $normalizedConstructs);
        }

        ObjectCache::popCache();

        return $normalizedConstructs;
    }

    /**
     * @throws BosNormalizerException
     * @throws \ReflectionException
     */
    private function normalizeData(mixed $data, ?string $group): array
    {
        $this->propertyNormalizer->cleanUp();
        $normalizedData = [];

        if (is_iterable($data) || $data instanceof \Traversable) {
            foreach ($data as $item) {
                $normalizedData[] = $this->normalizeData($item, $group);
            }
        } elseif (is_object($data)) {
            $normalizedData = $this->normalizeObject($data, $group);
        } else {
            throw new BosNormalizerException('Can only normalize an object or an array of objects. Input contains: ' . gettype($data));
        }

        return $normalizedData;
    }

    /**
     * Resets the caches.
     */
    private function cleanUpSession(): void
    {
        ObjectCache::clear();
        $this->propertyNormalizer->cleanUpSession();
    }
}
