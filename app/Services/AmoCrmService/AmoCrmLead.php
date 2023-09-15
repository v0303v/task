<?php

declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\LeadsFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\LeadModel;
use App\DTOs\BuyerDTO;

class AmoCrmLead extends AmoCrmOAuth
{
    public function createLead(BuyerDTO $buyerDTO, int $responsibleUserId, ContactModel $contact): ?LeadModel
    {
        $leadsService = $this->apiClient->leads();

        $lead = new LeadModel();
        $lead->setName('Cделка на имя: ' . $buyerDTO->firstName . ' ' . $buyerDTO->lastName)
            ->setCreatedAt($buyerDTO->createdAt)
            ->setResponsibleUserId($responsibleUserId)
            ->setClosestTaskAt((new AmoCrmTask())->checkWorkingHours($buyerDTO->createdAt));

        try {
            $leadModel = $leadsService->addOne($lead);

            $links = new LinksCollection();
            $links->add($contact);
            $this->apiClient->leads()->link($lead, $links);

            return $leadModel;
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }

    }

    public function checkForDoubles(string $phoneNumber): ?ContactModel
    {
        $contactIds = [];
        $phoneArr = [];
        $leads = $this->getSuccessLeads();
        foreach ($leads as $lead) {
            $contactIds[] = $lead->getContacts()[0]->id;
        }

        foreach ($contactIds as $contactId) {
            $contactModel = (new AmoCrmContact())->getOneContact($contactId);
            $phoneArr[] = $contactModel->getCustomFieldsValues()
                ->getBy('fieldCode', 'PHONE')
                ->getValues()[0]
                ->value;

            foreach ($phoneArr as $phone) {
                if ($phoneNumber === $phone) {
                    return $contactModel;
                }
            }
        }

        return null;
    }

    private function getSuccessLeads(): ?array
    {
        $successLeads = [];
        $leadFilters = new LeadsFilter();
        $leads = $this->apiClient->leads()->get($leadFilters, [EntityTypesInterface::CONTACTS]);

        foreach ($leads as $lead) {
            if (LeadModel::WON_STATUS_ID === $lead->getStatusId()) {
                $successLeads[] = $lead;
            }
        }

        return $successLeads;
    }
}
