<?php

namespace App\DTOs;

class BuyerDTO
{
    public string $first_name;
    public string $last_name;
    public int $age;
    public string $gender;
    public string $phone_number;

    public string $email;
    public string $tag;
    public int $created_at;

    public function __construct($first_name, $last_name, $age, $gender, $phone_number, $email, $tag, $created_at)
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->age = $age;
        $this->gender = $gender;
        $this->phone_number = $phone_number;
        $this->email = $email;
        $this->tag = $tag;
        $this->created_at = $created_at;
    }
}
