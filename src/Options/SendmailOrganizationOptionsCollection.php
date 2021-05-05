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
class SendmailOrganizationOptionsCollection extends AbstractOptions
{

    private $options;

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOrganizationOptions(string $id)
    {
        return new SendmailOrganizationOptions(
            $this->options[$id] ?? []
        );
    }
}
