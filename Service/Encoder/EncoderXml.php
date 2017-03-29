<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException;
use Exception;
use SimpleXMLElement;

class EncoderXml extends AbstractEncoder
{
    /** @var string */
    const DEFAULT_WRAP_ELEMENT = 'data';

    /** @var string */
    const EXCEPTION_PREFIX = 'Error when encoding XML: ';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return EncoderFactory::TYPE_XML;
    }

    /**
     * @inheritdoc
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     */
    public function encode($value)
    {
        if (!is_array($value)) {
            return null;
        }

        if (null === $this->wrapElement) {
            $this->wrapElement = static::DEFAULT_WRAP_ELEMENT;
        }

        $xmlData = new SimpleXMLElement(
            '<?xml version="1.0"?>' . '<' . $this->wrapElement . '></' . $this->wrapElement . '>'
        );

        try {
            $xmlData = $this->arrayToXml($value, $xmlData);
        } catch (Exception $e) {
            throw new BosNormalizerException(static::EXCEPTION_PREFIX . $e->getMessage());
        }

        $this->getError($xmlData->asXML());

        return $xmlData->asXML();
    }

    /**
     * @param array            $data
     * @param SimpleXMLElement $xmlData
     *
     * @return SimpleXMLElement
     */
    private function arrayToXml(array $data, SimpleXMLElement $xmlData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subNode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subNode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }

        return $xmlData;
    }

    /**
     * @param string $xmlData
     *
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosNormalizerException
     */
    private function getError($xmlData)
    {
        libxml_use_internal_errors(true);
        if (false === simplexml_load_string($xmlData)) {
            foreach(libxml_get_errors() as $error) {
                throw new BosNormalizerException(static::EXCEPTION_PREFIX . $error->message);
            }
        }
    }
}
