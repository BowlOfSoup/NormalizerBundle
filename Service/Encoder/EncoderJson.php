<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\NormalizerBundleException;

class EncoderJson extends AbstractEncoder
{
    /** @var string */
    const ERROR_NO_ERROR = 'No error';

    /** @var string */
    const EXCEPTION_PREFIX = 'Error when encoding JSON: ';

    /** @var int */
    private $options;

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return EncoderFactory::TYPE_JSON;
    }

    /**
     * @param int $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function encode($value)
    {
        if (null !== $this->wrapElement) {
            $value = array($this->wrapElement => $value);
        }

        $encodedValue = json_encode($value, (int) $this->options);

        $this->getError();

        return $encodedValue;
    }

    /**
     * Dumps error messages.
     *
     * @throws NormalizerBundleException
     */
    private function getError()
    {
        $errorMessage = json_last_error_msg();

        if (static::ERROR_NO_ERROR !== $errorMessage) {
            throw new NormalizerBundleException(static::EXCEPTION_PREFIX . $errorMessage);
        }
    }
}
