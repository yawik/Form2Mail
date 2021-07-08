<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Filter;

use Form2Mail\Options\ModuleOptions;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Filter\FormFrontendUri
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class FormFrontendUriFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): FormFrontendUri {
        return new FormFrontendUri(
            $container->get(ModuleOptions::class)->getFormFrontendUri()
        );
    }
}
