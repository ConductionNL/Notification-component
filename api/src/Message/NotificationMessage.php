<?php

namespace App\Message;

use Ramsey\Uuid\UuidInterface;

class NotificationMessage
{
    private UuidInterface $notificationId;

    public function __construct (UuidInterface $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    public function getNotificationId() : UuidInterface
    {
        return $this->notificationId;
    }
}
