<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 13.09.16
 * Time: 15:48
 */

namespace CultuurNet\UDB3\IISImporter\Event;

use \CultuurNet\UDB3\IISImporter\Exceptions;

class ParserV3 implements ParserInterface
{
    /**
     * @param string $xmlString
     * @return boolean
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
     * @param string $xmlString
     * @return \ValueObjects\String\
     */
    public function split($xmlString)
    {
        $arrXml = array();
        $reader = new \XMLReader();
        $reader->xml($xmlString);
        while( $reader->read() ) {
            if( $reader->localName === 'event' && $reader->nodeType === 1 ) {
                $arrXml[] = $reader->readOuterXml();
            }
        }
        print_r( $arrXml );

        return $arrXml;

        // TODO: Implement split() method.
    }

    private function getValidNameSpaces()
    {
        return [
            'http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.3/FINAL' => __DIR__ . '/../CdbXmlSchemes/CdbXSD3.3.xsd',
        ];
    }

    private function loadDOM($xml)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($xml);

        $namespaceURI = $dom->documentElement->namespaceURI;

        if (!array_key_exists($namespaceURI, $this->getValidNameSpaces())) {
            throw new Exceptions\UnexpectedNamespaceException(
                $namespaceURI,
                $this->getValidNameSpaces()
            );
        }
        $schema = $this->getValidNameSpaces()[$namespaceURI];

        $localName = $dom->documentElement->localName;
        $expectedLocalName = 'cdbxml';

        if ($localName !== $expectedLocalName) {
            throw new Exceptions\UnexpectedRootElementException(
                $localName,
                $expectedLocalName
            );
        }

        if (!$dom->schemaValidate($schema)) {
            throw new Exceptions\SchemaValidationException($namespaceURI);
        }

        return $dom;
    }
}
