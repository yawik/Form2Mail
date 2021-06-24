<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Options;

use Jobs\Entity\JobInterface;
use Laminas\Stdlib\AbstractOptions;
use Organizations\Entity\OrganizationInterface;

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

    /**
     * use `%s` as placeholder for the job/apply id
     *
     * @var string
     */
    private $formFrontendUri = '';

    /**
     * @var array
     */
    private $emailDomainsBlacklist = [];

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
     * Get formFrontendUri
     *
     * @return string
     */
    public function getFormFrontendUri(): string
    {
        return $this->formFrontendUri;
    }

    /**
     * Set formFrontendUri
     *
     * @param string $formFrontendUri
     */
    public function setFormFrontendUri(string $formFrontendUri): void
    {
        $this->formFrontendUri = $formFrontendUri;
    }

    /**
     * Get emailDomainsBlacklist
     *
     * @return array
     */
    public function getEmailDomainsBlacklist(): array
    {
        return $this->emailDomainsBlacklist;
    }

    /**
     * Set emailDomainsBlacklist
     *
     * @param array $emailDomainsBlacklist
     */
    public function setEmailDomainsBlacklist(array $emailDomainsBlacklist): void
    {
        $this->emailDomainsBlacklist = $emailDomainsBlacklist;
    }
}
