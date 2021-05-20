<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\DetailsController
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class DetailsControllerFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): DetailsController {
        $repos = $container->get('repositories');
        $helper = $container->get('ViewHelperManager');
        return new DetailsController(
            $repos->get('Jobs'),
            $repos->get('Organizations'),
            $helper->get('jobUrl'),
            $helper->get('serverUrl'),
            $container->get('Organizations\ImageFileCache\Manager')
        );
    }
}
