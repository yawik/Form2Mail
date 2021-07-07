<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugin;

use Form2Mail\Hydrator\ApplicationHydrator;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\Plugins\StoreApplication
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class StoreApplicationFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): StoreApplication {
        return new StoreApplication(
            $container->get('repositories')->get('Applications'),
            $container->get('HydratorManager')->get(ApplicationHydrator::class),
            $container->get('Applications/Events')
        );
    }
}
