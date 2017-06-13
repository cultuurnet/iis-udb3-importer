<?php

namespace CultuurNet\UDB3\IISImporter\CategorizationRules;

use ValueObjects\StringLiteral\StringLiteral;

class Category
{
    /**
     * @var StringLiteral
     */
    public $catId;

    /**
     * @var StringLiteral
     */
    public $type;

    /**
     * @var StringLiteral
     */
    public $label;

    /**
     * Category constructor.
     * @param StringLiteral $catId
     * @param StringLiteral $type
     * @param StringLiteral $label
     */
    public function __construct(StringLiteral $catId, StringLiteral $type, StringLiteral $label)
    {
        $this->catId = $catId;
        $this->type = $type;
        $this->label = $label;
    }
}
