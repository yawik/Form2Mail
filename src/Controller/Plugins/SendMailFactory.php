<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugins;

use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\Plugins\SendMail
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class SendMailFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): SendMail {
        return new SendMail(
            $container->get('Core/MailService')
        );
    }
}
