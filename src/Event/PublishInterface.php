<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;


interface PublishInterface
{
    /**
     * @param UUID $cdbid
     * @param \DateTime $datetime
     * @param StringLiteral $author
     * @param Url $url
     * @return void
     */
    public function publish(UUID $cdbid, \DateTime $datetime, StringLiteral $author, Url $url);
};
