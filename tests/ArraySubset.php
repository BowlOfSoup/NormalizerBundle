<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

/**
 * Constraint that asserts that the array it is evaluated for has a specified subset.
 *
 * Uses array_replace_recursive() to check if a key value subset is part of the
 * subject array.
 *
 * @codeCoverageIgnore
 */
class ArraySubset extends Constraint
{
    public function __construct(
        private iterable $subset,
        private readonly bool $strict = false,
    ) {
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     *
     * @codeCoverageIgnore
     */
    public static function assert(array|\ArrayAccess $subset, array|\ArrayAccess $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        $constraint = new self($subset, $checkForObjectIdentity);

        Assert::assertThat($array, $constraint, $message);
    }

    /**
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool
    {
        // type cast $other & $this->subset as an array to allow
        // support in standard array functions.
        $other = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);

        $patched = \array_replace_recursive($other, $this->subset);

        if ($this->strict) {
            $result = $other === $patched;
        } else {
            $result = $other == $patched;
        }

        if ($returnResult) {
            return $result;
        }

        if (!$result) {
            $f = new ComparisonFailure(
                $patched,
                $other,
                \var_export($patched, true),
                \var_export($other, true)
            );

            $this->fail($other, $description, $f);
        }

        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function toString(): string
    {
        return 'has the subset ' . $this->exporter()->export($this->subset);
    }

    /**
     * @throws InvalidArgumentException
     */
    #[\Override]
    protected function failureDescription(mixed $other): string
    {
        return 'an array ' . $this->toString();
    }

    private function toArray(iterable $other): array
    {
        if (\is_array($other)) {
            return $other;
        }

        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof \Traversable) {
            return \iterator_to_array($other);
        }

        // Keep BC even if we know that array would not be the expected one
        return (array) $other;
    }
}
