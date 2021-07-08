<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Form2Mail\Options\ModuleOptions;
use Form2Mail\Options\SendmailOrganizationOptionsCollection;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\SendMailController
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class SendMailControllerFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): SendMailController {
        $repos = $container->get('repositories');

        $controller = new SendMailController(
            $repos->get('Jobs'),
            $repos->get('Organizations')
        );

        $controller->setOrganizationOptions($container->get(SendmailOrganizationOptionsCollection::class));
        $controller->setModuleoOtions($container->get(ModuleOptions::class));

        return $controller;
    }
}
