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
        return new SendMailController(
            $container->get('Core/MailService')
        );
    }
}
