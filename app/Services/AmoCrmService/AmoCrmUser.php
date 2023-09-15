<?php

declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\UsersCollection;
use AmoCRM\Exceptions\AmoCRMApiException;

class AmoCrmUser extends AmoCrmOAuth
{
    public function getUsers() : ?UsersCollection
    {
        try {
            return $this->apiClient->users()->get();
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }

    public function getRandomUser(): int
    {
        $usersService = $this->getUsers();
        $rand = mt_rand(0, count((array)$usersService) - 1);
        return $usersService[$rand]->id;
    }
}
