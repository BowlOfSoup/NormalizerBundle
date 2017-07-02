<?php

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;
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
     *
     * @return string
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
            throw new BosSerializerException(static::EXCEPTION_PREFIX . $e->getMessage());
        }

        $this->getError($xmlData->asXML());

        return $this->makeDom($xmlData);
    }

    /**
     * @param array            $data
     * @param SimpleXMLElement $xmlData
     *
     * @return SimpleXMLElement
     */
    protected function arrayToXml(array $data, SimpleXMLElement $xmlData)
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
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     */
    protected function getError($xmlData)
    {
        $error = '';

        libxml_use_internal_errors(true);
        if (false === simplexml_load_string($xmlData)) {
            foreach(libxml_get_errors() as $error) {
                $error .= ', ' . $error->message;
            }
        }

        if (!empty($error)) {
            throw new BosSerializerException($error);
        }
    }

    /**
     * @param SimpleXMLElement $xmlData
     *
     * @return string
     */
    protected function makeDom(SimpleXMLElement $xmlData)
    {
        $domElement = dom_import_simplexml($xmlData);

        $domOutput = new \DOMDocument('1.0');
        $domOutput->formatOutput = true;

        $domElement = $domOutput->importNode($domElement, true);

        $domOutput->appendChild($domElement);

        return $domOutput->saveXML($domOutput, LIBXML_NOEMPTYTAG);
    }
}
