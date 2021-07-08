<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugin;

use Applications\Entity\Status;
use Applications\Listener\Events\ApplicationEvent;
use Applications\Repository\Application as ApplicationsRepository;
use Auth\Entity\AnonymousUser;
use Core\EventManager\EventManager;
use Form2Mail\Hydrator\ApplicationHydrator;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\View\Helper\Url;

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
    private $urlHelper;

    public function __construct(
        ApplicationsRepository $applications,
        ApplicationHydrator $hydrator,
        EventManager $appEvents,
        Url $urlHelper
    ) {
        $this->applications = $applications;
        $this->hydrator = $hydrator;
        $this->applicationEvents = $appEvents;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke($data, $files, $job, $org, $orgOptions, $moduleOptions)
    {
        /** @var \Applications\Entity\Application $application */
        $application = $this->applications->create();
        $application = $this->hydrator->hydrate([$data, $files], $application);
        $application->setJob($job);
        $application->setUser(new AnonymousUser());
        $application->setStatus(new Status());

        $this->applications->store($application);

        /** @var \Applications\Listener\Events\ApplicationEvent $event */
        $event = $this->applicationEvents->getEvent(ApplicationEvent::EVENT_APPLICATION_POST_CREATE);
        $event->setApplicationEntity($application);

        $this->applicationEvents->triggerEvent($event);

        return [
            'success' => true,
            'message' => 'Application stored successfully',
            'payload' => $data,
            'uri' => ($this->urlHelper)(
                'lang/applications/detail',
                ['id' => $application->getId()],
                [
                    'force_canonical' => true,
                    'query' => [
                        'token' => $application->getUser()->getToken(),
                    ]
                ]
            ),
        ];
    }
}
