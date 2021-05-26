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
class ModuleOptions extends AbstractOptions
{
    /** @var array */
    private $allowedOrigins = [];

    /** @var array */
    private $allowedMethods = [
        'sendmail' => 'POST',
        'details' => 'GET',
    ];

    private $doStoreApplications = false;


    /**
     * Get allowedOrigins
     *
     * @return array
     */
    public function getAllowedOrigins()
    {
        return $this->allowedOrigins;
    }

    /**
     * Set allowedOrigins
     *
     * @param array $allowedOrigins
     */
    public function setAllowedOrigins(array $allowedOrigins): void
    {
        $this->allowedOrigins = $allowedOrigins;
    }

    /**
     * Get allowedMethods
     *
     * @return array|string
     */
    public function getAllowedMethods(?string $routeName = null)
    {
        return
            $routeName
            ? ($this->allowedMethods[$routeName] ?? '')
            : $this->allowedMethods
        ;
    }

    /**
     * Set allowedMethods
     *
     * @param array $allowedMethods
     */
    public function setAllowedMethods(array $allowedMethods): void
    {
        $this->allowedMethods = $allowedMethods;
    }

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
