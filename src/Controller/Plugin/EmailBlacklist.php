<?php

/**
 * YAWIK Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class EmailBlacklist extends AbstractPlugin
{
    private $domains = [];

    public function __construct(array $domains = [])
    {
        $this->domains = $domains;
    }

    public function __invoke($emails)
    {
        return $this->check($emails);
    }

    public function check($emails)
    {
        if (!is_array($emails)) {
            $emails = [$emails];
        }

        foreach ($emails as $email) {
            if ($this->checkEmail($email)) {
                return true;
            }
        }

        return false;
    }

    public function checkEmail($email)
    {
        $parts = explode('@', $email, 2);
        $domain = array_pop($parts);

        return in_array($domain, $this->domains);
    }
}
