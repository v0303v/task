<?php
declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use App\DTOs\BuyerDTO;

class AmoCrmContact extends AmoCrmOAuth
{
    public function createContact(BuyerDTO $buyerDTO)
    {
        $contact = new ContactModel();
        $contact->setFirstName($buyerDTO->first_name)
            ->setLastName($buyerDTO->last_name)
            ->setIsMain(true)
            ->setCustomFieldsValues(
                (new CustomFieldsValuesCollection())
                    ->add(
                        (new MultitextCustomFieldValuesModel())
                            ->setFieldCode('EMAIL')
                            ->setValues(
                                (new MultitextCustomFieldValueCollection())
                                    ->add(
                                        (new MultitextCustomFieldValueModel())
                                            ->setValue($buyerDTO->email)
                                    )
                            )
                    )
                    ->add(
                        (new MultitextCustomFieldValuesModel())
                            ->setFieldCode('PHONE')
                            ->setValues(
                                (new MultitextCustomFieldValueCollection())
                                    ->add(
                                        (new MultitextCustomFieldValueModel())
                                            ->setValue($buyerDTO->phone_number)
                                    )
                            )
                    )
            );
        try {
            $contactModel = $this->api_client->contacts()->addOne($contact);
            return $contactModel->getId();
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }

    public function getOneContact(int $idContact): ?ContactModel
    {
        try {
            return $this->api_client->contacts()->getOne($idContact);
        } catch (AmoCRMMissedTokenException $e) {
            print_r($e);
            die;
        }

    }

    public function getContacts(): ?ContactsCollection
    {
        return $this->api_client->contacts()->get();
    }

    public function addGender(string $gender, int $idContact)
    {
        $contact = $this->getOneContact($idContact);
        $customFields = $contact->getCustomFieldsValues();
        $genderField = $customFields->getBy('fieldId', 2235077);
        if (empty($genderField)) {
            $genderField = (new TextCustomFieldValuesModel())
                ->setFieldId(2235077);
            $customFields->add($genderField);
        }

        $genderField->setValues(
            (new TextCustomFieldValueCollection())
                ->add((new TextCustomFieldValueModel())
                    ->setValue($gender))
        );

        try {
            $this->api_client->contacts()->updateOne($contact);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }

    public function addAge(int $age, int $idContact)
    {
        $contact = $this->getOneContact($idContact);
        $customFields = $contact->getCustomFieldsValues();
        $genderField = $customFields->getBy('fieldId', 2235027);

        if (empty($genderField)) {
            $genderField = (new NumericCustomFieldValuesModel())
                ->setFieldId(2235027);
            $customFields->add($genderField);
        }

        $genderField->setValues(
            (new NumericCustomFieldValueCollection())
                ->add((new NumericCustomFieldValueModel())
                    ->setValue($age))
        );

        try {
            $this->api_client->contacts()->updateOne($contact);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }

    private function getAllContactsPhones()
    {
        $phoneNumbers = [];
        $contactIds = [];
        try {
            $contacts = $this->api_client->contacts()->get();
        } catch (AmoCRMMissedTokenException $e) {
            print_r($e);
            die;
        }

        foreach ($contacts as $contact) {
            $phoneNumbers[] = $contact->getCustomFieldsValues()->getBy('fieldCode', 'PHONE')->getValues()[0]->value;
            $contactIds[] = $contact->getId();
        }

        return array_combine($contactIds, $phoneNumbers);
    }

    public function ifExistsContact(string $phoneNumber)
    {
        $contactsPhones = $this->getAllContactsPhones();

        foreach ($contactsPhones as $idContact => $phone) {

            if ($phoneNumber == $phone) {
                return $idContact;
            }
        }
        return 0;
    }
}
