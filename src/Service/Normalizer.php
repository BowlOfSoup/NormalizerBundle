<?php

namespace BowlOfSoup\NormalizerBundle\Service;

use BowlOfSoup\NormalizerBundle\Model\ObjectCache;
use BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor;
use BowlOfSoup\NormalizerBundle\Service\Normalize\MethodNormalizer;
use BowlOfSoup\NormalizerBundle\Service\Normalize\PropertyNormalizer;

class Normalizer
{
    /** @var \BowlOfSoup\NormalizerBundle\Service\Extractor\ClassExtractor */
    protected $classExtractor;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalize\PropertyNormalizer */
    private $propertyNormalizer;

    /** @var \BowlOfSoup\NormalizerBundle\Service\Normalize\MethodNormalizer */
    private $methodNormalizer;

    public function __construct(
        ClassExtractor $classExtractor,
        PropertyNormalizer $propertyNormalizer,
        MethodNormalizer $methodNormalizer
    ) {
        $this->classExtractor = $classExtractor;
        $this->propertyNormalizer = $propertyNormalizer;
        $this->methodNormalizer = $methodNormalizer;
    }

    /**
     * Normalize an object or an array of objects, for a specific group.
     *
     * @param mixed $data
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     * @throws \ReflectionException
     */
    public function normalize($data, string $group = null): array
    {
        if (empty($data)) {
            return [];
        }

        $this->cleanUp();
        $normalizedData = [];

        if (is_iterable($data) || $data instanceof \Traversable) {
            foreach ($data as $item) {
                $normalizedData[] = $this->normalize($item, $group);
            }
        } else {
            $normalizedData = $this->normalizeObject($data, $group);
        }
        $this->cleanUp();

        return $normalizedData;
    }

    /**
     * Get properties for given object, annotations per property and begin normalizing.
     *
     * In this method, 'new Normalize(array())' is used for PHP < 5.5 support.
     * Normally we should use 'Normalize::class'
     *
     * @throws \ReflectionException
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     */
    public function normalizeObject(object $object, ?string $group): array
    {
        $normalizedConstructs = [];
        $objectName = get_class($object);
        $objectIdentifier = $this->classExtractor->getId($object);

        ObjectCache::setObjectByName($objectName, $objectIdentifier);

        // If cached return previously cached normalized object.
        if (null !== $objectIdentifier && ObjectCache::hasObjectByNameAndIdentifier($objectName, $objectIdentifier)) {
            return ObjectCache::getObjectByNameAndIdentifier($objectName, $objectIdentifier);
        }
        ObjectCache::resetObjectByNameAndIdentifier($objectName, $objectIdentifier);

        $normalizedClassProperties = $this->propertyNormalizer->normalize($this, $objectName, $object, $group);
        $normalizedConstructs = array_merge($normalizedConstructs, ...$normalizedClassProperties);

        $normalizedClassMethods = $this->methodNormalizer->normalize($this, $objectName, $object, $group);
        $normalizedConstructs = array_merge($normalizedConstructs, ...$normalizedClassMethods);

        if (null !== $objectIdentifier) {
            ObjectCache::setNormalizedPropertiesByNameAndIdentifier($objectName, $objectIdentifier, $normalizedConstructs);
        }

        ObjectCache::popCache();

        return $normalizedConstructs;
    }

    /**
     * Resets the caches.
     */
    private function cleanUp(): void
    {
        ObjectCache::clear();
        $this->propertyNormalizer->cleanUp();
    }
}
