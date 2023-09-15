<?php
declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\ContactsFilter;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\LeadModel;
use App\DTOs\BuyerDTO;
use DateTime;

class AmoCrmLead extends AmoCrmOAuth
{
    public function createLead(BuyerDTO $buyerDTO, int $idContact, int $responsibleUserId)
    {
        $leadsService = $this->api_client->leads();

        $lead = new LeadModel();
        $lead->setName('Cделка на имя: ' . $buyerDTO->first_name . ' ' . $buyerDTO->last_name)
            ->setCreatedAt($buyerDTO->created_at)
            ->setResponsibleUserId($responsibleUserId)
            ->setClosestTaskAt($this->checkWorkingHours($buyerDTO->created_at));

        try {
            $leadModel = $leadsService->addOne($lead);

            $contact = (new AmoCrmContact())->getOneContact($idContact);
            $links = new LinksCollection();
            $links->add($contact);
            $this->api_client->leads()->link($lead, $links);

            return $leadModel->getId();
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }

    }

    public function getLeads(): ?LeadsCollection
    {
        return $this->api_client->leads()->get();
    }

    public function getLeadById(int $idLead): ?LeadModel
    {
        return $this->api_client->leads()->getOne($idLead);
    }

    public function checkWorkingHours(int $created_at): int
    {
        $dateTime = new DateTime();
        $new_created_at = $dateTime->setTimestamp($created_at)->modify('+4 days')->getTimestamp();
        $dayOfWeek = $dateTime->setTimestamp($new_created_at)->format('l');
        $timeOfDay = $dateTime->setTimestamp($new_created_at)->format('H');

        if ('Sunday' == $dayOfWeek) {

            if (FALSE === in_array($timeOfDay, range(9, 18))) {

                if (9 < $timeOfDay) {
                    $dateTime->setTimestamp($new_created_at)
                        ->modify('+1 day')
                        ->modify(' +' . (9 - $timeOfDay) . ' hours')
                        ->getTimestamp();
                }

                if (18 > $timeOfDay) {
                    $dateTime->setTimestamp($new_created_at)
                        ->modify('+1 day')
                        ->modify(' +' . ($timeOfDay - 18) . ' hours')
                        ->getTimestamp();
                }

            }

            return $dateTime->setTimestamp($new_created_at)->modify('+ 1 day')->getTimestamp();
        }

        if ('Saturday' == $dayOfWeek) {

            if (FALSE === in_array($timeOfDay, range(9, 18))) {

                if (9 < $timeOfDay) {
                    $dateTime->setTimestamp($new_created_at)
                        ->modify('+2 day')
                        ->modify(' +' . (9 - $timeOfDay) . ' hours')
                        ->getTimestamp();
                }

                if (18 > $timeOfDay) {
                    $dateTime->setTimestamp($new_created_at)
                        ->modify('+2 day')
                        ->modify(' +' . ($timeOfDay - 18) . ' hours')
                        ->getTimestamp();
                }

            }

            return $dateTime->setTimestamp($new_created_at)->modify('+ 2 days')->getTimestamp();
        }

        return $new_created_at;
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
            $contactModel = (new AmoCrmContact())->getOneContact($contactId);
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
