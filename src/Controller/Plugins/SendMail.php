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

        $files = $this->getRequest()->getFiles()->toArray();

        // normalite json data
        /** @var \Core\Mail\HTMLTemplateMessage $mail */
        $vars = $this->normalizeJsonData($json);
        $vars['job'] = $job;
        $vars['org'] = $org;
        $vars['photo'] = isset($files['photo']) ? 1 : 0;
        $mail = $this->mails->get('htmltemplate');
        $mail->setTemplate('form2mail/mail/conduent');
        $mail->setVariables($vars);
        $mail->setSubject($job ? sprintf('Bewerbung auf %s', $job->getTitle()) : 'Initiativbewerbung');

        $mail->addTo($to);

        // Attachments handling
        $files = $this->getRequest()->getFiles()->toArray();

        $message = new MimeMessage();
        $html = new MimePart($mail->getBodyText());
        $html->type = Mime::TYPE_HTML;
        $html->disposition = Mime::DISPOSITION_INLINE;
        $html->charset = 'utf-8';
        $message->addPart($html);

        $vcard = $this->createVcard($vars['user'], $files['photo'] ?? null);
        $attachment = new MimePart($vcard);
        $attachment->type = Mime::TYPE_TEXT;
        $attachment->charset = 'utf8';
        $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
        $attachment->filename = 'kontakt.vcf';
        $message->addPart($attachment);

        if (isset($files['photo']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $files['photo'];
            $photo = new MimePart(fopen($file['tmp_name'], 'r'));
            $photo->disposition = Mime::DISPOSITION_INLINE;
            $photo->id = 'photo';
            $photo->type = mime_content_type($file['tmp_name']);
            $photo->filename = $file['name'];
            $photo->encoding = Mime::ENCODING_BASE64;
            $message->addPart($photo);
        }

        if (isset($files['attached']) && count($files['attached'])) {
            foreach ($files as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $attachment = new MimePart(fopen($file['tmp_name'], 'r'));
                $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                $attachment->type = mime_content_type($file['tmp_name']);
                $attachment->filename = $file['name'];
                $attachment->encoding = Mime::ENCODING_BASE64;
                $message->addPart($attachment);
            }
        }

        $new = new Message();
        $new->setBody($message);
        $new->setSubject($mail->getSubject());
        $new->addTo($to);

        $mail = $new;

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

    private function createVcard(InfoInterface $user, ?array $photo)
    {
        $card = new VCard([
            'FN' => $user->getDisplayname(false),
            'N' => [$user->getLastName(), $user->getFirstName(), '', '', ''],
            'EMAIL' => $user->getEmail(),
        ]);

        if ($user->getBirthYear()) {
            $card->add('BDAY', $user->getBirthYear() . '-' . $user->getBirthMonth() . '-' . $user->getBirthDay());
        }
        if ($user->getCity()) {
            $card->add(
                'ADDR',
                ['', '', $user->getStreet(), $user->getCity(), '', $user->getPostalCode(), $user->getCountry()],
                ['TYPE' => 'home']
            );
        }
        if ($user->getPhone()) {
            $card->add(
                'TEL',
                $user->getPhone(),
                ['TYPE' => 'home']
            );
        }
        if ($photo && $photo['error'] === UPLOAD_ERR_OK) {
            $data = base64_encode(file_get_contents($photo['tmp_name']));
            $mime = mime_content_type($photo['tmp_name']);
            $img = "data:$mime;base64,$data";

            $card->add(
                'PHOTO',
                $img,
                ['VALUE' => 'URI']
            );
        }

        return $card->serialize();
    }
}
