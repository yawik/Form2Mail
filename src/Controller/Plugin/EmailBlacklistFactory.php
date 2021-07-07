<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugin;

use Form2Mail\Options\ModuleOptions;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Controller\Plugin\EmailBlacklist
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class EmailBlacklistFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): EmailBlacklist {
        /** @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        return new EmailBlacklist(
            $moduleOptions->getEmailDomainsBlacklist()
        );
    }
}
