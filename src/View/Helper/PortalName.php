<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\View\Helper;

use Laminas\Form\View\Helper\AbstractHelper;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class PortalName extends AbstractHelper
{

    public function __invoke(string $portal)
    {
        switch ($portal) {
            case "jobs.kliniken.de":
            case "stellenmarkt.sueddeutsche.de":
            case "www.yourfirm.de":
                return "auf https://$portal";

            default:
                return 'im Internet';
        }
    }
}
