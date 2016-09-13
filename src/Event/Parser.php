<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 13.09.16
 * Time: 15:48
 */

namespace CultuurNet\UDB3\IISImporter\Event;

class Parser implements ParserInterface
{
    /**
     * @param string $xmlString
     * @return boolean
     */
    public function validate($xmlString)
    {
        $this->loadDOM($xmlString);
        return true;
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

        if (!$dom->schemaValidate($schema)) {
            throw new SchemaValidationException($namespaceURI);
        }

        return $dom;
    }
}
