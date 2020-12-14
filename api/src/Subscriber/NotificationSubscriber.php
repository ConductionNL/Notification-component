<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Component;
use App\Entity\Notification;
use App\Service\InstallService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $em;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $em, NotificationService $notificationService)
    {
        $this->em = $em;
        $this->notificationService = $notificationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['Notification', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function Notification(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $contentType = $event->getRequest()->headers->get('accept');
        $route = $event->getRequest()->attributes->get('_route');
        $notification = $event->getControllerResult();

        if ($method != 'POST' || !($notification instanceof Notification)) {
            return;
        }

        $this->notificationService->publish($notification);
        //$component['message'] = $results;


        return;
    }
}
