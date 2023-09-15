<?php

declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\CatalogElementsCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\CatalogElementsFilter;
use AmoCRM\Models\Customers\CustomerModel;
use AmoCRM\Models\LeadModel;

class AmoCrmProduct extends AmoCrmOAuth
{
    public function getProducts(): ?CatalogElementsCollection
    {
        $catalogsCollection = $this->apiClient->catalogs()->get();
        $catalog = $catalogsCollection->getBy('name', 'Товары');

        $catalogElementsCollection = new CatalogElementsCollection();
        $catalogElementsService = $this->apiClient->catalogElements($catalog->getId());
        $catalogElementsFilter = new CatalogElementsFilter();

        try {
            $catalogElementsCollection = $catalogElementsService->get($catalogElementsFilter);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
        return $catalogElementsCollection;
    }

    public function linkProductLead(LeadModel $lead): void
    {
        $catalogElementsCollection = $this->getProducts();

        foreach ($catalogElementsCollection as $catalogElement) {
            $links = new LinksCollection();
            $links->add($catalogElement);
            try {
                $this->apiClient->leads()->link($lead, $links);
            } catch (AmoCRMApiException $e) {
                print_r($e);
                die;
            }
        }
    }

    public function linkProductCustomer(CustomerModel $customer): void
    {
        $catalogElementsCollection = $this->getProducts();

        foreach ($catalogElementsCollection as $catalogElement) {
            $links = new LinksCollection();
            $links->add($catalogElement);
            try {
                $this->apiClient->customers()->link($customer, $links);
            } catch (AmoCRMApiException $e) {
                print_r($e);
                die;
            }
        }
    }
}
