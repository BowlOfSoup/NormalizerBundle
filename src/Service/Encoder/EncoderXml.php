<?php

declare(strict_types=1);

namespace BowlOfSoup\NormalizerBundle\Service\Encoder;

use BowlOfSoup\NormalizerBundle\Exception\BosSerializerException;

class EncoderXml extends AbstractEncoder
{
    /** @var string */
    public const DEFAULT_WRAP_ELEMENT = 'data';

    /** @var string */
    protected const EXCEPTION_PREFIX = 'Error when encoding XML: ';

    public function getType(): string
    {
        return EncoderFactory::TYPE_XML;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     * @throws \Exception
     */
    public function encode($value): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        if (null === $this->wrapElement) {
            $this->wrapElement = static::DEFAULT_WRAP_ELEMENT;
        }

        $xmlData = new \SimpleXMLElement(
            '<?xml version="1.0"?><' . $this->wrapElement . '></' . $this->wrapElement . '>'
        );

        try {
            $xmlData = $this->arrayToXml($value, $xmlData);
        } catch (\Exception $e) {
            throw new BosSerializerException(static::EXCEPTION_PREFIX . $e->getMessage());
        }

        $this->getError($xmlData->asXML());

        return $this->makeDom($xmlData);
    }

    protected function arrayToXml(array $data, \SimpleXMLElement $xmlData): \SimpleXMLElement
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subNode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subNode);
            } else {
                $xmlData->addChild((string) $key, htmlspecialchars((string) $value));
            }
        }

        return $xmlData;
    }

    /**
     * @throws \BowlOfSoup\NormalizerBundle\Exception\BosSerializerException
     */
    protected function getError(string $xmlData): void
    {
        $errorMessage = '';

        libxml_use_internal_errors(true);
        if (false === simplexml_load_string($xmlData)) {
            foreach (libxml_get_errors() as $error) {
                $errorMessage .= ', ' . $error->message;
            }
        }

        if (!empty($errorMessage)) {
            throw new BosSerializerException($errorMessage);
        }
    }

    protected function makeDom(\SimpleXMLElement $xmlData): string
    {
        $domElement = dom_import_simplexml($xmlData);

        $domOutput = new \DOMDocument('1.0');
        $domOutput->formatOutput = true;

        $domElement = $domOutput->importNode($domElement, true);

        $domOutput->appendChild($domElement);

        return $domOutput->saveXML($domOutput, LIBXML_NOEMPTYTAG);
    }
}
