<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\AmoCrmService\AmoCrmContact;
use App\Services\AmoCrmService\AmoCrmCustomer;
use App\Services\AmoCrmService\AmoCrmLead;
use App\Services\AmoCrmService\AmoCrmOAuth;
use App\Services\AmoCrmService\AmoCrmProduct;
use App\Services\AmoCrmService\AmoCrmTask;
use App\Services\AmoCrmService\AmoCrmUser;
use App\DTOs\BuyerDTO;

class AmoCrmService extends AmoCrmOAuth
{
    public function run(BuyerDTO $buyerDto)
    {
        $responsibleUserId = (new AmoCrmUser())->getRandomUser();

        //Если контакт уже есть и существующая сделка в успешном статусе (142)
        $successContactId = (new AmoCrmLead())->checkForDoubles($buyerDto->phone_number);
        if (isset($successContactId) && 0 !== $successContactId) {
            $lastCustomerId = (new AmoCrmCustomer())->createCustomer($buyerDto, $successContactId, $responsibleUserId);
            (new AmoCrmProduct())->linkProductCustomer($lastCustomerId);
            (new AmoCrmTask())->createTaskCustomer($buyerDto, $lastCustomerId, $responsibleUserId);

            return json_encode([
                'success' => 'Контакт уже существует, сделка в успешном статусе, будет создан покупатель'
            ], JSON_UNESCAPED_UNICODE);
        }

        // есть ли контакт уже есть в аккаунте
        $contactId = (new AmoCrmContact())->ifExistsContact($buyerDto->phone_number);
        if (isset($contactId) && 0 !== $contactId) {
            $lastLead = (new AmoCrmLead())->createLead($buyerDto, $contactId, $responsibleUserId);
            (new AmoCrmTask)->createTaskLead($buyerDto, $lastLead, $responsibleUserId);
            (new AmoCrmProduct())->linkProductLead($lastLead);

            return json_encode([
                'success' => 'Контакт уже существует, создана новая сделка по этому контакту'
            ], JSON_UNESCAPED_UNICODE);
        }

        $lastContactId = (new AmoCrmContact())->createContact($buyerDto);
        (new AmoCrmContact())->addGender($buyerDto->gender, $lastContactId);
        (new AmoCrmContact())->addAge($buyerDto->age, $lastContactId);
        $lastLead = (new AmoCrmLead())->createLead($buyerDto, $lastContactId, $responsibleUserId);
        (new AmoCrmTask)->createTaskLead($buyerDto, $lastLead, $responsibleUserId);
        (new AmoCrmProduct())->linkProductLead($lastLead);

        return json_encode([
            'success' => 'Контакт создан'
        ], JSON_UNESCAPED_UNICODE);
    }
}
