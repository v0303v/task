<?php

declare(strict_types=1);

namespace App\Services\AmoCrmService;

use AmoCRM\Collections\TasksCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Helpers\EntityTypesInterface;
use AmoCRM\Models\LeadModel;
use AmoCRM\Models\TaskModel;
use App\DTOs\BuyerDTO;
use DateTime;

class AmoCrmTask extends AmoCrmOAuth
{
    public function createTaskLead(BuyerDTO $buyerDTO, LeadModel $leadModel, int $responsibleUserId): void
    {
        $duration = 4 * 24 * 60 * 60;
        $tasksCollection = new TasksCollection();
        $task = new TaskModel();
        $task->setText('Задача на сделку №' . $leadModel->getId())
            ->setCompleteTill($this->checkWorkingHours($buyerDTO->createdAt))
            ->setEntityType(EntityTypesInterface::LEADS)
            ->setEntityId($leadModel->getId())
            ->setDuration($duration)
            ->setResponsibleUserId($responsibleUserId);
        $tasksCollection->add($task);

        try {
            $tasksService = $this->apiClient->tasks();
            $tasksCollection = $tasksService->add($tasksCollection);
        } catch (AmoCRMApiException $e) {
            print_r($e);
            die;
        }
    }

    public function checkWorkingHours(int $createdAt): int
    {
        $dateTime = new DateTime();
        $newCreatedAt = $dateTime->setTimestamp($createdAt)->modify('+4 days')->getTimestamp();
        $dayOfWeek = $dateTime->setTimestamp($newCreatedAt)->format('l');
        $timeOfDay = $dateTime->setTimestamp($newCreatedAt)->format('H');

        if ('Sunday' == $dayOfWeek) {

            if (false === in_array($timeOfDay, range(9, 18))) {

                if (9 < $timeOfDay) {
                    $dateTime->setTimestamp($newCreatedAt)
                        ->modify('+ 1 day')
                        ->modify(' +' . (10 - $timeOfDay) . ' hours')
                        ->getTimestamp();
                }

                if (18 > $timeOfDay) {
                    $dateTime->setTimestamp($newCreatedAt)
                        ->modify('+ 1 day')
                        ->modify(' - ' . ($timeOfDay - 19) . ' hours')
                        ->getTimestamp();
                }

            }

            return $dateTime->setTimestamp($newCreatedAt)->modify('+ 1 day')->getTimestamp();
        }

        if ('Saturday' == $dayOfWeek) {

            if (false === in_array($timeOfDay, range(9, 18))) {

                if (9 < $timeOfDay) {
                    $dateTime->setTimestamp($newCreatedAt)
                        ->modify('+ 2 day')
                        ->modify(' - ' . (10 - $timeOfDay) . ' hours')
                        ->getTimestamp();
                }

                if (18 > $timeOfDay) {
                    $dateTime->setTimestamp($newCreatedAt)
                        ->modify('+2 day')
                        ->modify(' - ' . ($timeOfDay - 19) . ' hours')
                        ->getTimestamp();
                }

            }

            return $dateTime->setTimestamp($newCreatedAt)->modify('+ 2 days')->getTimestamp();
        }

        return $newCreatedAt;
    }
}
