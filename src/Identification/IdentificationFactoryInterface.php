<?php

namespace CultuurNet\UDB3\IISImporter\Identification;

use ValueObjects\Identity\UUID;

interface IdentificationFactoryInterface
{
    /**
     * @param string $user
     * @return UUID
     */
    public function getUserId($user);
}
