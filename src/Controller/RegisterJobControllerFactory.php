<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Form2Mail\Entity\UserMetaData;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\RegisterJobController
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class RegisterJobControllerFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): RegisterJobController {
        $helper = $container->get('ViewHelperManager');
        return new RegisterJobController(
            $helper->get('jobUrl')
        );
    }
}
