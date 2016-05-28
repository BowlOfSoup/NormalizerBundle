<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

class EncoderYaml extends AbstractEncoder
{
    /** @var int */
    private $encoding;

    /** @var int */
    private $lineBreak;

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return EncoderFactory::TYPE_YAML;
    }

    /**
     * @param int $encoding
     *
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @param int $lineBreak
     *
     * @return $this
     */
    public function setLineBreak($lineBreak)
    {
        $this->lineBreak = $lineBreak;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function encode($value)
    {
        if (!function_exists('yaml_emit')) {
            return null;
        }

        if (!empty($this->wrapElement)) {
            $value = array($this->wrapElement => $value);
        }

        $encodedValue = yaml_emit($value, (int) $this->encoding, (int) $this->lineBreak);

        $this->getError($encodedValue);

        return $encodedValue;
    }

    /**
     * @param string
     */
    private function getError($encodedValue)
    {
        if (!yaml_parse($encodedValue)) {
            var_dump('Yaml encoding went wrong!');
        }
    }
}
