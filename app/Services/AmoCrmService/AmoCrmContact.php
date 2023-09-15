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
    public function createContact(BuyerDTO $buyerDTO): ?ContactModel
    {
        $contact = new ContactModel();
        $contact->setFirstName($buyerDTO->firstName)
            ->setLastName($buyerDTO->lastName)
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
                                            ->setValue($buyerDTO->phoneNumber)
                                    )
                            )
                    )
                    ->add(
                        (new TextCustomFieldValuesModel())
                            ->setFieldId(2235077)
                            ->setValues(
                                (new TextCustomFieldValueCollection())
                                    ->add(
                                        (new TextCustomFieldValueModel())
                                            ->setValue($buyerDTO->gender)
                                    )
                            )
                    )
                    ->add(
                        (new NumericCustomFieldValuesModel())
                            ->setFieldId(2235027)
                            ->setValues(
                                (new NumericCustomFieldValueCollection())
                                    ->add(
                                        (new NumericCustomFieldValueModel())
                                            ->setValue($buyerDTO->age)
                                    )
                            )
                    )
            );
        try {
            return $this->apiClient->contacts()->addOne($contact);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }

    public function getOneContact(int $idContact): ?ContactModel
    {
        try {
            return $this->apiClient->contacts()->getOne($idContact);
        } catch (AmoCRMMissedTokenException $e) {
            print_r($e);
            die;
        }

    }

    public function getContacts(): ?ContactsCollection
    {
        return $this->apiClient->contacts()->get();
    }
}
