<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugin;

use Applications\Listener\Events\ApplicationEvent;
use Applications\Repository\Application as ApplicationsRepository;
use Auth\Entity\AnonymousUser;
use Core\EventManager\EventManager;
use Form2Mail\Hydrator\ApplicationHydrator;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class StoreApplication extends AbstractPlugin
{

    private $applications;
    private $hydrator;
    private $applicationEvents;

    public function __construct(
        ApplicationsRepository $applications,
        ApplicationHydrator $hydrator,
        EventManager $appEvents
    ) {
        $this->applications = $applications;
        $this->hydrator = $hydrator;
        $this->applicationEvents = $appEvents;
    }

    public function __invoke($data, $job, $org, $orgOptions, $moduleOptions)
    {
        /** @var \Applications\Entity\Application $application */
        $application = $this->applications->create();
        $application = $this->hydrator->hydrate($data, $application);
        $application->setJob($job);
        $application->setUser(new AnonymousUser());

        $this->applications->store($application);

        /** @var \Applications\Listener\Events\ApplicationEvent $event */
        $event = $this->applicationEvents->getEvent(ApplicationEvent::EVENT_APPLICATION_POST_CREATE);
        $event->setApplicationEntity($application);

        $this->applicationEvents->triggerEvent($event);

        return [
            'success' => true,
            'message' => 'Application stored successfully',
            'payload' => $data,
            'uri' => $application->getId(),
            'token' => $application->getUser()->getToken(),
        ];
    }
}
