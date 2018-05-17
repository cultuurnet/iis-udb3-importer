<?php

namespace CultuurNet\UDB3\IISImporter\CategorizationRules;

use ValueObjects\StringLiteral\StringLiteral;

interface CategorizationRulesInterface
{
    /**
     * @param StringLiteral $value
     * @return Category
     */
    public function getFlandersRegion(StringLiteral $value);

    /**
     * @return Category
     */
    public function getUndeterminedType();
}
