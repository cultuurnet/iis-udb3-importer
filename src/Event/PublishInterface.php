<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use ValueObjects\StringLiteral\StringLiteral;

interface PublishInterface
{
    /**
     * @param StringLiteral $cdbid
     */
    public function publish(StringLiteral $cdbid);
}
