<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Listener;

use Form2Mail\Filter\FormFrontendUri;
use Jobs\Entity\AtsMode;
use Jobs\Listener\Events\JobEvent;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class InjectApplyUrlListener
{
    private $uriFilter = '';

    public function __construct(FormFrontendUri $uriFilter)
    {
        $this->uriFilter = $uriFilter;
    }

    public function __invoke(JobEvent $event)
    {
        $job = $event->getJobEntity();
        $ats = $job->getAtsMode();

        if (!$ats->isIntern()) {
            return;
        }

        $ats->setMode(AtsMode::MODE_URI);
        $ats->setUri(($this->uriFilter)($job));
    }
}
