<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;

class EncoderJson extends AbstractEncoder
{
    /** @var string */
    protected const EXCEPTION_PREFIX = 'Error when encoding JSON: ';

    /** @var string */
    protected const ERROR_NO_ERROR = 'No error';

    /** @var int|null */
    private $options = null;

    public function getType(): string
    {
        return EncoderFactory::TYPE_JSON;
    }

    /**
     * Set json_encode options, keep in mind that options need to be divided like JSON_HEX_TAG | JSON_HEX_QUOT.
     */
    public function setOptions(int $options): void
    {
        $this->options = $options;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     */
    public function encode($value): string
    {
        if (null !== $this->wrapElement) {
            $value = [$this->wrapElement => $value];
        }

        $encodedValue = json_encode($value, (int) $this->options);

        $this->getError();

        return $encodedValue;
    }

    /**
     * Throws error messages.
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     */
    protected function getError(): void
    {
        if (!$this->jsonLastErrorMsgExists()) {
            return;
        }

        $errorMessage = json_last_error_msg();
        if (static::ERROR_NO_ERROR !== $errorMessage) {
            throw new BosSerializerException(static::EXCEPTION_PREFIX . $errorMessage);
        }
    }

    protected function jsonLastErrorMsgExists(): bool
    {
        return function_exists('json_last_error_msg');
    }
}
