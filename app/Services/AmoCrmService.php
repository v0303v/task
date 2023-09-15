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
    public function execute(BuyerDTO $buyerDto)
    {
        $responsibleUserId = (new AmoCrmUser())->getRandomUser();

        //Если контакт уже есть и существующая сделка в успешном статусе (142)
        $successContact = (new AmoCrmLead())->checkForDoubles($buyerDto->phoneNumber);
        if (isset($successContact) && null !== $successContact) {
            $customer = (new AmoCrmCustomer())->createCustomer($buyerDto, $successContact, $responsibleUserId);
            (new AmoCrmProduct())->linkProductCustomer($customer);

            return json_encode([
                'success' => 'Контакт уже существует, сделка в успешном статусе, будет создан покупатель'
            ], JSON_UNESCAPED_UNICODE);
        }

        // есть ли контакт уже есть в аккаунте, не в статусе "Успешно реализована"
        if (true === is_null($successContact)){
            return json_encode([
                'success' => 'Контакт уже существует, Cделка по этому контакту еще не реализована'
            ], JSON_UNESCAPED_UNICODE);
        }

        $contact = (new AmoCrmContact())->createContact($buyerDto);
        $lastLead = (new AmoCrmLead())->createLead($buyerDto, $responsibleUserId, $contact);
        (new AmoCrmTask)->createTaskLead($buyerDto, $lastLead, $responsibleUserId);
        (new AmoCrmProduct())->linkProductLead($lastLead);

        return json_encode([
            'success' => 'Контакт создан'
        ], JSON_UNESCAPED_UNICODE);
    }
}
