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

    private $doStoreApplications = false;

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
     * @var bool
     */
    private $injectApplyUri = false;

    /**
     * @var ?string
     */
    private $initialApplicationJobTitle;

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

    /**
     * Get injectApplyUrl
     *
     * @return bool
     */
    public function doInjectApplyUri(): bool
    {
        return $this->injectApplyUri;
    }

    /**
     * Set injectApplyUrl
     *
     * @param bool $injectApplyUri
     */
    public function setInjectApplyUri(bool $injectApplyUri): void
    {
        $this->injectApplyUri = $injectApplyUri;
    }

    /**
     * Get initialApplicationJobTitle
     *
     * @return ?string
     */
    public function getInitialApplicationJobTitle(): ?string
    {
        return $this->initialApplicationJobTitle;
    }

    /**
     * Set initialApplicationJobTitle
     *
     * @param ?string $initialApplicationJobTitle
     */
    public function setInitialApplicationJobTitle(?string $initialApplicationJobTitle): void
    {
        $this->initialApplicationJobTitle = $initialApplicationJobTitle;
    }
}
