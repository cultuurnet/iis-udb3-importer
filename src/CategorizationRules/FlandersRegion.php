<?php

namespace CultuurNet\UDB3\IISImporter\CategorizationRules;

use \SimpleXMLElement;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class FlandersRegion implements CategorizationRulesInterface
{
    const PREFIX = 'c';

    /**
     * @var SimpleXMLElement
     */
    protected $taxonomy;

    /**
     * @param StringLiteral $value
     * @return Category
     */
    public function getCategoryFromValue(StringLiteral $value)
    {
        $xpath = $this->createXPath($value);
        $terms = $this->taxonomy->xpath((string) $xpath);

        if (sizeof($terms) > 0) {
            $attributes = $terms[0]->attributes();
            $cnetId = new StringLiteral((string) $attributes['id']);
            $domain = new StringLiteral((string) $attributes['domain']);
            $label =  new StringLiteral((string) $attributes['labelnl']);
            return new Category($cnetId, $domain, $label);

        } else {
            $fallbackXpath = $this->createFallbackXPath($value);
            $terms = $this->taxonomy->xpath((string) $fallbackXpath);

            if (sizeof($terms) > 0) {
                $attributes = $terms[0]->attributes();
                $cnetId = new StringLiteral((string) $attributes['id']);
                $domain = new StringLiteral((string) $attributes['domain']);
                $label =  new StringLiteral((string) $attributes['labelnl']);
                return new Category($cnetId, $domain, $label);

            } else {
                return null;
            }
        }
    }

    /**
     * FlandersRegion constructor.
     * @param Url $taxonomy
     * @param Url $namespace
     */
    public function __construct(Url $taxonomy, Url $namespace)
    {
        $this->taxonomy = simplexml_load_file((string) $taxonomy);
        $this->taxonomy->registerXPathNamespace(self::PREFIX, (string) $namespace);
    }

    /**
     * @param StringLiteral $value
     * @return StringLiteral
     */
    private function createXPath(StringLiteral $value)
    {
        $zipCity = explode(' ', $value, 2);

        $zip = $zipCity[0];
        $city =  explode("(", $zipCity[1], 2)[0];

        return new StringLiteral('//c:term[contains(@label,\'' . $zip . '\') and contains(@label, \''. $city .'\')]');
    }

    /**
     * @param StringLiteral $value
     * @return StringLiteral
     */
    private function createFallbackXPath(StringLiteral $value)
    {
        $zipCity = explode(' ', $value, 2);
        $zip = $zipCity[0];

        return new StringLiteral('//c:term[contains(@label,\'' . $zip . '\')]');
    }
}
