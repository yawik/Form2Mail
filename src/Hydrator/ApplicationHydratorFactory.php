<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Hydrator;

use Core\Service\FileManager;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Hydrator\ApplicationHydrator
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class ApplicationHydratorFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): ApplicationHydrator {
        return new ApplicationHydrator(
            $container->get(FileManager::class)
        );
    }
}
