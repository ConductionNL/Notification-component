<?php


namespace App\MessageHandler;


use App\Entity\Subscription;
use App\Message\NotificationMessage;
use App\Repository\NotificationRepository;
use App\Repository\SubscriptionRepository;
use GuzzleHttp\Client;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotificationMessageHandler implements MessageHandlerInterface
{
    private ParameterBagInterface $params;
    private NotificationRepository $notificationRepository;
    private SubscriptionRepository $subscriptionRepository;
    private array $headers;
    private array $guzzleConfig;
    private Client $messageClient;

    public function __construct(ParameterBagInterface $params, NotificationRepository $notificationRepository, SubscriptionRepository $subscriptionRepository)
    {
        $this->params = $params;
        $this->notificationRepository = $notificationRepository;
        $this->subscriptionRepository = $subscriptionRepository;

        $this->headers = [
            'Content-Type'  => 'application/json',

            // NLX
            'X-NLX-Request-Application-Id' => $this->params->get('app_commonground_id'), // the id of the application performing the request
            // NL Api Strategie
            'Accept-Crs'  => 'EPSG:4326',
            'Content-Crs' => 'EPSG:4326',
        ];
        $this->guzzleConfig = [
            // Base URI is used with relative requests
            'http_errors' => false,
            // You can set any number of default request options.
            'timeout' => 4000.0,
            // To work with NLX we need a couple of default headers
            'headers' => $this->headers,
            // Do not check certificates
            'verify' => false,
        ];

        $this->messageClient = new Client($this->guzzleConfig);

    }

    public function __invoke(NotificationMessage $message)
    {
        $notification = $this->notificationRepository->find($message->getNotificationId());

        $topic = $notification->getTopic();
        $subscriptions = $this->subscriptionRepository->findBy(['topic' => $topic]);

        $type = 'com.example.someevent';
        $source = '/context';
        $id = $notification->getId()->toString();

        foreach($subscriptions as $subscription){
            if($subscription instanceof Subscription)
            {
                $secret = $subscription->getSecret();

                /**@TODO: Detect the type of secret and do something with it */
                $headers = $this->headers;
                if($secret){
                    $headers['Authorization'] = $secret;
                }
                $resource = [
                    'specversion' => '1.0',
                    'type'      => $type,
                    'source'    => $source,
                    'id'        => $id,
                    'time'      => new \DateTime('now'),
                    'datacontenttype' => 'application/json',
                    'data' =>
                        [
                            'resource'  => $notification->getResource(),
                            'action'    => $notification->getAction(),
                        ],
                ];

                $response = $this->messageClient->post(
                    $subscription->getCallback(),
                    [
                        'body' => json_encode($resource),
                        'headers'=>$headers,
                    ]
                );
            }
        }

    }
}
