<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugins;

use Auth\Entity\Info;
use Core\Mail\MailService;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Http\Response;
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class SendMail extends AbstractPlugin
{

    private $mails;

    public function __construct(MailService $mails)
    {
        $this->mails = $mails;
    }

    public function __invoke($data, $job, $org, $options)
    {

        if (!$job) {
            $to  = $org->getUser()->getInfo()->getEmail();
        } else {
            $to = $job->getUser()->getInfo()->getEmail() ?? $org->getUser()->getInfo()->getEmail();
        }

        // normalite json data
        /** @var \Core\Mail\HTMLTemplateMessage $mail */
        $vars = $this->normalizeJsonData($data);
        $vars['job'] = $job;
        $vars['org'] = $org;
        $mail = $this->mails->get('htmltemplate');
        $mail->setTemplate('form2mail/mail/conduent');
        $mail->setVariables($vars);
        $mail->setSubject($job ? sprintf('Bewerbung auf %s', $job->getTitle()) : 'Initiale Bewerbung');

        $mail->addTo($to);

        // Attachments handling
        $files = $this->getController()->getRequest()->getFiles()->toArray();
        if (isset($files['attached']) && count($files['attached'])) {
            $message = new MimeMessage();
            $html = new MimePart($mail->getBodyText());
            $html->type = Mime::TYPE_HTML;
            $html->disposition = Mime::DISPOSITION_INLINE;
            $html->charset = 'utf-8';
            $message->addPart($html);

            foreach ($files['attached'] as $file) {
                $attachment = new MimePart(fopen($file['tmp_name'], 'r'));
                $attachment->type = mime_content_type($file['tmp_name']);
                $attachment->filename = $file['name'];
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                $attachment->encoding = Mime::ENCODING_BASE64;
                $message->addPart($attachment);
            }

            $new = new Message();
            $new->setBody($message);
            $new->setSubject($mail->getSubject());
            $new->addTo($to);

            $mail = $new;
        }

        try {
            $this->mails->send($mail);
        } catch (\Laminas\Mail\Exception\ExceptionInterface $e) {
            /** @var \Throwable $e */
            return $e;
        }

        return [
            'success' => true,
            'message' => 'Mail send successfully',
            'payload' => $data,
            'mail' => $mail->toString()
        ];
    }

    private function normalizeJsonData($json)
    {
        $user = new Info();
        foreach ($json['user'] as $key => $value) {
            $setter = "set$key";
            if (is_callable([$user, $setter])) {
                $user->$setter($value);
            }
        }

        return [
            'user' => $user,
            'summary' => $json['summary'] ?? '',
            'extras' => $json['extras'] ?? [],
        ];
    }
}
