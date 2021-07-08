<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Listener;

use Form2Mail\Filter\FormFrontendUri;
use Psr\Container\ContainerInterface;

/**
 * Factory for \Form2Mail\Listener\InjectApplyUrlListener
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class InjectApplyUrlListenerFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): InjectApplyUrlListener {
        return new InjectApplyUrlListener(
            $container->get('FilterManager')->get(FormFrontendUri::class)
        );
    }
}
