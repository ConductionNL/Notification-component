<?php

namespace App\Service;


use App\Entity\Notification;
use App\Message\NotificationMessage;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationService
{

    private MessageBusInterface $messageBus;
    private CommonGroundService $commonGroundService;


    public function __construct(MessageBusInterface $messageBus, CommonGroundService $commonGroundService){
        $this->messageBus = $messageBus;
        $this->commonGroundService = $commonGroundService;
    }
    public function publish(Notification $notification): NotificationMessage
    {
        $message = new NotificationMessage($notification->getId());

        $this->messageBus->dispatch($message);

        return $message;
    }
}
