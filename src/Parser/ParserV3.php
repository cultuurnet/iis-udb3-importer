<?php

namespace CultuurNet\UDB3\IISImporter\Parser;

use CultuurNet\UDB3\IISImporter\Exceptions\SchemaValidationException;
use CultuurNet\UDB3\IISImporter\Exceptions\UnexpectedNamespaceException;
use CultuurNet\UDB3\IISImporter\Exceptions\UnexpectedRootElementException;
use ValueObjects\StringLiteral\StringLiteral;

class ParserV3 implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public function validate($xmlString)
    {
        try {
            $this->loadDOM($xmlString);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function split($xmlString)
    {
        $eventList = array();
        $reader = new \XMLReader();
        $reader->XML($xmlString);
        while ($reader->read()) {
            if ($reader->localName === 'event' && $reader->nodeType === 1) {
                $singleEvent = $this->getXmlDeclaration() .
                    $this->getCdbxmlStartTag() .
                    $reader->readOuterXml() .
                    $this->getCdbxmlEndTag();

                $singleXml = simplexml_load_string($singleEvent);
                $externalId = (string) $singleXml->event[0]['externalid'];

                $eventList[$externalId] = new StringLiteral($singleEvent);
            }
        }

        return $eventList;
    }

    /**
     * @param $xml
     * @return \DOMDocument
     */
    public function loadDOM($xml)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        $namespaceURI = $dom->documentElement->namespaceURI;

        if (!array_key_exists($namespaceURI, $this->getValidNameSpaces())) {
            throw new UnexpectedNamespaceException(
                $namespaceURI,
                $this->getValidNameSpaces()
            );
        }
        $schema = $this->getValidNameSpaces()[$namespaceURI];

        $localName = $dom->documentElement->localName;
        $expectedLocalName = 'cdbxml';

        if ($localName !== $expectedLocalName) {
            throw new UnexpectedRootElementException(
                $localName,
                $expectedLocalName
            );
        }

        try {
            $throwValidationException = !$dom->schemaValidate($schema);
        } catch (\Exception $exception) {
            $throwValidationException = true;
        }
        if ($throwValidationException) {
            throw new SchemaValidationException($namespaceURI);
        }

        return $dom;
    }

    /**
     * @return array
     */
    private function getValidNameSpaces()
    {
        return [
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL' => __DIR__ . '/../CdbXmlSchemes/CdbXSD3.3.xsd',
        ];
    }

    /**
     * @return string
     */
    private function getXmlDeclaration()
    {
        return '<?xml version="1.0" encoding="utf-8"?>';
    }

    /**
     * @return string
     */
    private function getCdbxmlStartTag()
    {
        return '<cdbxml xsi:schemaLocation="http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL/CdbXSD.xsd" xmlns="http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
    }

    /**
     * @return string
     */
    private function getCdbxmlEndTag()
    {
        return  '</cdbxml>';
    }
}
