<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Console;

use Core\Mail\MailService;
use Form2Mail\Entity\UserMetaData;
use Form2Mail\Filter\FormFrontendUri;
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
    private $uriFilter;

    public static function getConsoleUsage()
    {
        return [
            'form2mail invite-recruiters' => 'Invite all registered recruiters',
            'Sends the "invite-recruiter" mail to all recruiters registered through the "extract" action',
            'that were not receiving a mail yet.',
            "",
            ['--limit=INT', 'Send the invite email only to the first INT recruiters'],
            ['--text', 'Use text emails instead of html formatted mails.'],
            "",
            'Following variables are passed to the mail template ',
            ['user', 'User entity of the recruiter'],
            ['org', 'Organization entity'],
            ['job', 'The job entity'],
            ['formLink', 'the link to the application form (see ModuleOptions::getFormFrontendUri())'],
            ['options', 'ModuleOptions instance'],
            "",
            "",
        ];
    }

    public function __construct(
        UserMetaDataRepository $meta,
        MailService $mails,
        ModuleOptions $options,
        FormFrontendUri $uriFilter
    ) {
        $this->meta = $meta;
        $this->mails = $mails;
        $this->options = $options;
        $this->uriFilter = $uriFilter;
    }

    public function indexAction()
    {
        $console = $this->getConsole();
        $console->writeLine('Fetching uninvited recruiters...');

        $data = $this->meta->findMetaDataOfInvitableUsers(
            (int) $this->params('limit')
        );
        if ($this->params('text', false)) {
            $template = 'form2mail/mail/invite-recruiter-text';
            $mailType = 'stringtemplate';
        } else {
            $template = 'form2mail/mail/invite-recruiter';
            $mailType = 'htmltemplate';
        }

        $console->writeLine('Found ' . count($data) . ' recruiters.');

        /** @var UserMetaData $meta */
        foreach ($data as $meta) {
            $user = $meta->getUser();
            $job = $meta->getJob();
            $link = ($this->uriFilter)($job);
            $variables = [
                'user' => $user,
                'org' => $user->getOrganization()->getOrganization(),
                'job' => $meta->getJob(),
                'formLink' => $link,
                'options' => $this->options,
            ];

            $mail = $this->mails->build($mailType);
            $mail->addTo($user->getInfo()->getEmail());
            $mail->setSubject('Erstellen Sie Ihr Bewerbungs-Formular');
            $mail->setVariables($variables);
            $mail->setTemplate($template);

            try {
                $this->mails->send($mail);
                exit;
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
