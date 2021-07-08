<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Console;

use Form2Mail\Entity\UserMetaData;
use Form2Mail\Filter\FormFrontendUri;
use Form2Mail\Options\ModuleOptions;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\Console\InviteRecruiterController
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class InviteRecruiterControllerFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): InviteRecruiterController {
        return new InviteRecruiterController(
            $container->get('repositories')->get(UserMetaData::class),
            $container->get('Core/MailService'),
            $container->get(ModuleOptions::class),
            $container->get('FilterManager')->get(FormFrontendUri::class)
        );
    }
}
