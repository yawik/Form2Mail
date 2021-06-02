<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Console;

use Core\Mail\MailService;
use Form2Mail\Entity\UserMetaData;
use Form2Mail\Options\ModuleOptions;
use Form2Mail\Repository\UserMetaDataRepository;
use Laminas\Mvc\Console\Controller\AbstractConsoleController;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class InviteRecruiterController extends AbstractConsoleController
{

    private $mails;
    private $meta;
    private $options;

    public function __construct(
        UserMetaDataRepository $meta,
        MailService $mails,
        ModuleOptions $options
    ) {
        $this->meta = $meta;
        $this->mails = $mails;
        $this->options = $options;
    }

    public function indexAction()
    {
        $console = $this->getConsole();
        $console->writeLine('Fetching uninvited recruiters...');

        $data = $this->meta->findMetaDataOfInvitableUsers();

        $console->writeLine('Found ' . count($data) . ' recruiters.');

        /** @var UserMetaData $meta */
        foreach ($data as $meta) {
            $user = $meta->getUser();
            $job = $meta->getJob();
            $link = $this->options->getFormFrontendUri() . '?job=' . $job->getApplyId();
            $variables = [
                'user' => $user,
                'org' => $user->getOrganization()->getOrganization(),
                'job' => $meta->getJob(),
                'formLink' => $link,
                'options' => $this->options,
            ];

            $mail = $this->mails->build('htmltemplate');
            $mail->addTo($user->getInfo()->getEmail());
            $mail->setSubject('Erstellen Sie Ihr Bewerbungs-Formular');
            $mail->setVariables($variables);
            $mail->setTemplate('form2mail/mail/invite-recruiter');

            try {
                $this->mails->send($mail);
                $meta->setState(UserMetaData::STATE_PENDING);
                $this->meta->store($meta);
                $console->writeLine(' - Mail send: ' . $user->getInfo()->getEmail());
            } catch (\Throwable $e) {
                $console->writeLine(' - Fail: ' . $user->getInfo()->getEmail());
                $console->writeLine('   ' . get_class($e) . ': ' . $e->getMessage());
            }
        }
        $console->writeLine('[done]');
    }
}
