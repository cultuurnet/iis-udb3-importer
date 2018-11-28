<?php

namespace CultuurNet\UDB3\IISImporter\Identification;

use ValueObjects\Identity\UUID;

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
        $result = array_search($user, array_column($this->users, 'name'));
        if ($result) {
            $userId = $this->users[$result]['id'];
            $userUuid = UUID::fromNative($userId);
            var_dump($userUuid);
            return $userUuid;
        } else {
            return null;
        }
    }
}
