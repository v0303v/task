<?php

declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\Customers\CustomerModel;
use AmoCRM\Models\TagModel;
use App\DTOs\BuyerDTO;

class AmoCrmCustomer extends AmoCrmOAuth
{
    public function createCustomer(BuyerDTO $buyerDTO, ContactModel $contact, int $responsibleUserId): ?CustomerModel
    {
        $customersService = $this->apiClient->customers();
        $contactsService = $this->apiClient->contacts();

        $customer = new CustomerModel();
        $customer->setResponsibleUserId($responsibleUserId)
            ->setClosestTaskAt((new AmoCrmTask())->checkWorkingHours($buyerDTO->createdAt))
            ->setTags((new TagsCollection())
                ->add(
                    (new TagModel())
                        ->setName($buyerDTO->tag)
                )
            );

        try {
            $customer = $customersService->addOne($customer);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }

        $contact->setIsMain(false);
        $links = new LinksCollection();
        $links->add($contact);

        try {
            $customersService->link($customer, $links);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }

        return $customer;
    }
}
