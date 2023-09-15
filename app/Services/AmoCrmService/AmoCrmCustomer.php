<?php
declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\LinksCollection;
use AmoCRM\Collections\TagsCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\Customers\CustomerModel;
use AmoCRM\Models\TagModel;
use App\DTOs\BuyerDTO;

class AmoCrmCustomer extends AmoCrmOAuth
{
    public function createCustomer(BuyerDTO $buyerDTO, int $idContact, int $responsibleUserId)
    {
        $customersService = $this->api_client->customers();
        $contactsService = $this->api_client->contacts();

        $customer = new CustomerModel();
        $customer->setResponsibleUserId($responsibleUserId)
                ->setClosestTaskAt((new AmoCrmLead())
                ->checkWorkingHours($buyerDTO->created_at))
                ->setTags((new TagsCollection())
                    ->add(
                        (new TagModel())
                            ->setName($buyerDTO->tag))
            );

        try {
            $customer = $customersService->addOne($customer);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }

        try {
            $contact = $contactsService->getOne($idContact);
            $contact->setIsMain(false);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }
        $links = new LinksCollection();
        $links->add($contact);
        try {
            $customersService->link($customer, $links);
        } catch (AmoCRMApiException $e) {
            printError($e);
            die;
        }

        return $customer->getId();
    }

    public function getCustomerById(int $idCustomer): ?CustomerModel
    {
        return $this->api_client->customers()->getOne($idCustomer);
    }

    public function checkForDoubles(string $phoneNumber)
    {
        $contactIds = [];
        $phoneArr = [];
        $leads = $this->getSuccessLeads();
        foreach ($leads as $lead) {
            $contactIds[] = $lead->getContacts()[0]->id;
        }

        foreach ($contactIds as $contactId) {
            $contactModel = $this->getCustomerById($contactId);
            $phoneArr[] = $contactModel->getCustomFieldsValues()
                ->getBy('fieldCode', 'PHONE')
                ->getValues()[0]
                ->value;

            foreach ($phoneArr as $phone) {
                if ($phoneNumber == $phone) {
                    return $contactId;
                }
            }
        }

        return 0;
    }

    private function getSuccessLeads(): ?array
    {
        $successLeads = [];
        $contactFilters = new ContactsFilter();
        $leads = $this->api_client->leads()->get($contactFilters, [EntityTypesInterface::CONTACTS]);

        foreach ($leads as $lead) {

            if (142 === $lead->getStatusId()) {
                $successLeads[] = $lead;
            }
        }

        return $successLeads;
    }
}
