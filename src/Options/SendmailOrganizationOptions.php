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
    private $sendConfirmEmail = false;
    private $confirmEmailSubject = '';
    private $confirmEmailTemplate = '';

    public function setSendConfirmEmail(bool $flag): void
    {
        $this->sendConfirmEmail = $flag;
    }

    public function shouldSendConfirmEmail(): bool
    {
        return $this->sendConfirmEmail;
    }

    /**
     * Get confirmEmailSubject
     *
     * @return string
     */
    public function getConfirmEmailSubject(): string
    {
        return $this->confirmEmailSubject;
    }

    /**
     * Set confirmEmailSubject
     *
     * @param string $confirmEmailSubject
     */
    public function setConfirmEmailSubject(string $confirmEmailSubject): void
    {
        $this->confirmEmailSubject = $confirmEmailSubject;
    }

    /**
     * Get confirmEmailTemplate
     *
     * @return string
     */
    public function getConfirmEmailTemplate(): string
    {
        return $this->confirmEmailTemplate;
    }

    /**
     * Set confirmEmailTemplate
     *
     * @param string $confirmEmailTemplate
     */
    public function setConfirmEmailTemplate(string $confirmEmailTemplate): void
    {
        $this->confirmEmailTemplate = $confirmEmailTemplate;
    }
}
