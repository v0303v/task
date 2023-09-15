<?php
declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\CatalogElementsCollection;
use AmoCRM\Collections\CatalogsCollection;
use AmoCRM\Collections\LinksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Filters\CatalogElementsFilter;

class AmoCrmProduct extends AmoCrmOAuth
{
    public function getProducts(): ?CatalogElementsCollection
    {
        $catalogsCollection = $this->api_client->catalogs()->get();
        $catalog = $catalogsCollection->getBy('name', 'Товары');

        $catalogElementsCollection = new CatalogElementsCollection();
        $catalogElementsService = $this->api_client->catalogElements($catalog->getId());
        $catalogElementsFilter = new CatalogElementsFilter();

        try {
            $catalogElementsCollection = $catalogElementsService->get($catalogElementsFilter);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
        return $catalogElementsCollection;
    }

    public function linkProductLead(int $idLead)
    {
        $catalogElementsCollection = $this->getProducts();

        $lead = (new AmoCrmLead())->getLeadById($idLead);

        foreach ($catalogElementsCollection as $catalogElement) {
            $links = new LinksCollection();
            $links->add($catalogElement);
            try {
                $this->api_client->leads()->link($lead, $links);
            } catch (AmoCRMApiException $e) {
                print_r($e);
                die;
            }
        }
    }

    public function linkProductCustomer(int $idCustomer)
    {
        $catalogElementsCollection = $this->getProducts();

        $lead = (new AmoCrmCustomer())->getCustomerById($idCustomer);

        foreach ($catalogElementsCollection as $catalogElement) {
            $links = new LinksCollection();
            $links->add($catalogElement);
            try {
                $this->api_client->customers()->link($lead, $links);
            } catch (AmoCRMApiException $e) {
                print_r($e);
                die;
            }
        }
    }
}
