<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;

class EncoderJson extends AbstractEncoder
{
    /** @var string */
    const ERROR_NO_ERROR = 'No error';

    /** @var string */
    const EXCEPTION_PREFIX = 'Error when encoding JSON: ';

    /** @var int */
    private $options;

    /** @var int */
    private $depth = 512;

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return EncoderFactory::TYPE_JSON;
    }

    /**
     * Set json_encode options, keep in mind that options need to be divided like JSON_HEX_TAG | JSON_HEX_QUOT.
     *
     * @param int $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param int $depth
     */
    public function setDepth($depth = 512)
    {
        $this->depth = $depth;
    }

    /**
     * @inheritdoc
     */
    public function encode($value)
    {
        if (null !== $this->wrapElement) {
            $value = array($this->wrapElement => $value);
        }

        $encodedValue = json_encode($value, (int) $this->options, $this->depth);

        $this->getError();

        return $encodedValue;
    }

    /**
     * Throws error messages.
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     */
    protected function getError()
    {
        if ($this->jsonLastErrorMsgExists()) {
            $errorMessage = json_last_error_msg();
        } else {
            $errorMessage = $this->getJsonErrorMessage();
        }

        if (static::ERROR_NO_ERROR !== $errorMessage) {
            throw new BosSerializerException(static::EXCEPTION_PREFIX . $errorMessage);
        }
    }

    /**
     * @return bool
     */
    protected function jsonLastErrorMsgExists()
    {
        return function_exists('json_last_error_msg');
    }

    /**
     * Get last JSON error message: PHP < 5.5.0 support.
     *
     * @return string
     */
    private function getJsonErrorMessage()
    {
        $errors = array(
            JSON_ERROR_NONE => 'No error',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );

        $error = json_last_error();

        return isset($errors[$error]) ? $errors[$error] : static::ERROR_NO_ERROR;
    }
}
