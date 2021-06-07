<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugin;

use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\Plugin\RegisterJob
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class RegisterJobFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): RegisterJob {
        $repositories = $container->get('repositories');

        return new RegisterJob(
            $repositories->get('Auth/User'),
            $repositories->get('Organizations'),
            $repositories->get('Jobs')
        );
    }
}
