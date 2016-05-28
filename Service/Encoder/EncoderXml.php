<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use Exception;
use SimpleXMLElement;

class EncoderXml extends AbstractEncoder
{
    /** @var string */
    const DEFAULT_WRAP_ELEMENT = 'data';

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return EncoderFactory::TYPE_XML;
    }

    /**
     * @inheritdoc
     */
    public function encode($value)
    {
        if (!is_array($value)) {
            return null;
        }

        if (null === $this->wrapElement) {
            $this->wrapElement = static::DEFAULT_WRAP_ELEMENT;
        }

        $wrapElement = '<' . $this->wrapElement . '></' . $this->wrapElement . '>';

        try {
            $xmlData = new SimpleXMLElement('<?xml version="1.0"?>' . $wrapElement);
            $xmlData = $this->arrayToXml($value, $xmlData);
        } catch (Exception $e) {
            var_dump($e->getMessage());
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
     */
    private function getError($xmlData)
    {
        libxml_use_internal_errors(true);
        if (false === simplexml_load_string($xmlData)) {
            foreach(libxml_get_errors() as $error) {
                var_dump($error->message);
            }
        }
    }
}
