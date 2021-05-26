<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Options;

use Laminas\Stdlib\AbstractOptions;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class SendmailOrganizationOptions extends AbstractOptions
{

    private $doStoreApplications = false;

    /**
     * Get doStoreApplication
     *
     * @return bool
     */
    public function doStoreApplications(): bool
    {
        return $this->doStoreApplications;
    }

    /**
     * Set doStoreApplication
     *
     * @param bool $doStoreApplication
     */
    public function setDoStoreApplications(bool $doStoreApplications): void
    {
        $this->doStoreApplications = $doStoreApplications;
    }
}
