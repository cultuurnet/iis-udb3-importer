<?php

namespace CultuurNet\UDB3\IISImporter\Identification;

class IdentificationFactory implements IdentificationFactoryInterface
{
    /**
     * @var array
     */
    private $users;

    /**
     * IdentificationFactory constructor.
     * @param $users
     */
    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * @inheritdoc
     */
    public function getUserId($user)
    {
        // TODO: Implement getUserId() method.
    }
}
