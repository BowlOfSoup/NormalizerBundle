<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

class EncoderJson extends AbstractEncoder
{
    /** @var string */
    const ERROR_NO_ERROR = 'No error';

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
     */
    private function getError()
    {
        $errorMessage = json_last_error_msg();

        if (static::ERROR_NO_ERROR !== $errorMessage) {
            var_dump($errorMessage);
        }
    }
}
