<?php
declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\TasksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\TaskModel;
use App\DTOs\BuyerDTO;

class AmoCrmTask extends AmoCrmOAuth
{
    public function createTaskLead(BuyerDTO $buyerDTO, int $idLead, int $responsibleUserId)
    {
        $duration = 4 * 24 * 60 * 60;
        $tasksCollection = new TasksCollection();
        $task = new TaskModel();
        $task->setText('Задача на сделку №' . $idLead)
            ->setCompleteTill((new AmoCrmLead())->checkWorkingHours($buyerDTO->created_at))
            ->setEntityType(EntityTypesInterface::LEADS)
            ->setEntityId($idLead)
            ->setDuration($duration)
            ->setResponsibleUserId($responsibleUserId);
        $tasksCollection->add($task);

        try {
            $tasksService = $this->api_client->tasks();
            $tasksCollection = $tasksService->add($tasksCollection);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }

    public function createTaskCustomer(BuyerDTO $buyerDTO, int $idCustomer, int $responsibleUserId)
    {
        $duration = 4 * 24 * 60 * 60;
        $tasksCollection = new TasksCollection();
        $task = new TaskModel();
        $task->setText('Задача на сделку с покупателем №' . $idCustomer)
            ->setCompleteTill((new AmoCrmLead())->checkWorkingHours($buyerDTO->created_at))
            ->setEntityType(EntityTypesInterface::CUSTOMERS)
            ->setEntityId($idCustomer)
            ->setDuration($duration)
            ->setResponsibleUserId($responsibleUserId);
        $tasksCollection->add($task);

        try {
            $tasksService = $this->api_client->tasks();
            $tasksCollection = $tasksService->add($tasksCollection);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }
}
