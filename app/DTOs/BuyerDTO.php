<?php

declare(strict_types=1);

namespace App\DTOs;

class BuyerDTO
{
    public string $firstName;
    public string $lastName;
    public int $age;
    public string $gender;
    public string $phoneNumber;

    public string $email;
    public string $tag;
    public int $createdAt;

    public function __construct(string $firstName,
                                string $lastName,
                                int $age,
                                string $gender,
                                string $phoneNumber,
                                string $email,
                                string $tag,
                                int $createdAt)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->age = $age;
        $this->gender = $gender;
        $this->phoneNumber = $phoneNumber;
        $this->email = $email;
        $this->tag = $tag;
        $this->createdAt = $createdAt;
    }
}
