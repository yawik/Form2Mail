<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Auth\Entity\Info;
use Auth\Entity\InfoInterface;
use Core\Mail\MailService;
use Form2Mail\Options\SendmailOrganizationOptionsCollection;
use Jobs\Repository\Job as JobsRepository;
use Organizations\Repository\Organization as OrganizationRepository;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Json\Json;
use Laminas\Mail\Message;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Mime;
use Laminas\Mime\Part as MimePart;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Sabre\VObject\Component\VCard;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class SendMailController extends AbstractActionController
{
    const ERROR_NO_POST = 'NO_POST';
    const ERROR_NO_REF = 'NO_REF';
    const ERROR_NO_ENTITY = 'NO_ENTITY';
    const ERROR_INVALID_JSON = 'INVALID_JSON';
    const ERROR_MAIL_FAILED = 'MAIL_FAILED';

    protected static $errors = [
        self::ERROR_NO_POST => 'Must use POST request',
        self::ERROR_INVALID_JSON => 'Invalid json',
        self::ERROR_NO_REF => 'Missing job or organization reference',
        self::ERROR_NO_ENTITY => 'No job or organization found',
        self::ERROR_MAIL_FAILED => 'Sending of mail failed',
    ];

    private $mails;
    private $jobs;
    private $orgs;
    private $organizationOptions;

    public function __construct(MailService $mails, JobsRepository $jobs, OrganizationRepository $orgs)
    {
        $this->mails = $mails;
        $this->jobs = $jobs;
        $this->orgs = $orgs;
    }

    public function setOrganizationOptions(SendmailOrganizationOptionsCollection $options)
    {
        $this->organizationOptions = $options;
    }

    public function getOrganizationOptions()
    {
        if (!$this->organizationOptions) {
            $this->organizationOptions = new SendmailOrganizationOptionsCollection();
        }

        return $this->organizationOptions;
    }

    public function indexAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->getResponse()->getHeaders()->addHeaderLine('Allow', Request::METHOD_POST);
            return $this->createErrorModel(self::ERROR_NO_POST, Response::STATUS_CODE_405);
        }

        $data = $this->params()->fromPost('application');
        try {
            $json = Json::decode($data, Json::TYPE_ARRAY);
        } catch (\Laminas\Json\Exception\ExceptionInterface $e) {
            /** @var \Throwable $e */
            return $this->createErrorModel(
                self::ERROR_INVALID_JSON,
                Response::STATUS_CODE_400,
                ['error' => $e->getMessage()]
            );
        }

        $applyId = $json['job'] ?? $json['org'];

        if (!$applyId) {
            return $this->createErrorModel(self::ERROR_NO_REF, Response::STATUS_CODE_400);
        }

        $job = $this->jobs->findOneBy(['applyId' => $applyId]) ?? $this->jobs->find($applyId);

        if (!$job) {
            $org = $this->orgs->findOneBy(['id' => $applyId]);

            if (!$org) {
                return $this->createErrorModel(
                    self::ERROR_NO_ENTITY,
                    Response::STATUS_CODE_400,
                    ['ref' => $applyId]
                );
            }
            $to  = $org->getUser()->getInfo()->getEmail();
        } else {
            $org = $job->getOrganization();
            $to = $job->getUser()->getInfo()->getEmail() ?? $org->getUser()->getInfo()->getEmail();
        }

        $options = $this->getOrganizationOptions()->getOrganizationOptions($org->getId());

        // normalite json data
        /** @var \Core\Mail\HTMLTemplateMessage $mail */
        $vars = $this->normalizeJsonData($json);
        $vars['job'] = $job;
        $vars['org'] = $org;
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

        if (isset($files) && count($files)) {
            foreach ($files as $key => $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if ($key !== 'attached' && $key !== 'photo') {
                    continue;
                }
                $attachment = new MimePart(fopen($file['tmp_name'], 'r'));
                if ($key === 'photo') {
                    $attachment->id = $key;
                    $attachment->disposition = Mime::DISPOSITION_INLINE;
                }else{
                    $attachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                }
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
            return $this->createErrorModel(self::ERROR_MAIL_FAILED, Response::STATUS_CODE_500, ['error' => $e->getMessage()]);
        }

        $extras = [
            'payload' => $json,
            'mail' => $mail->toString(),
        ];

        if ($options->shouldSendConfirmEmail() && ($emailAddress = $vars['user']->getEmail())) {
            $mail = $this->mails->get('htmltemplate');
            $mail->addTo($emailAddress);
            $mail->setSubject($options->getConfirmEmailSubject());
            $mail->setTemplate($options->getConfirmEmailTemplate());
            $mail->setVariables([
                'org' => $org,
                'job' => $job,
                'photo' => isset($files['photo]']),
                'recruiter' => $job ? $job->getUser() : $org->getUser(),
                'applicant' => $vars['user'],
            ]);
            try {
                $this->mails->send($mail);
                $extras['confirmMailSuccess'] = true;
                $extras['confirmMail'] = $mail->toString();
            } catch (\Throwable $e) {
                $extras['confirmMailSuccess'] = false;
                $extras['confirmMail'] = sprintf(
                    '[%s] %s',
                    get_class($e),
                    $e->getMessage()
                );
            }
        }

        return new JsonModel([
            'success' => true,
            'message' => 'Mail send successfully',
            'extras' => $extras
        ]);
    }

    protected function createErrorModel(string $type, $code = null, ?array $extras = null)
    {
        $this->getResponse()->setStatusCode($code ?? Response::STATUS_CODE_500);

        $result = [
            'success' => false,
            'message' => self::$errors[$type] ?? $type,
        ];

        if ($extras) {
            $result['extras'] = $extras;
        }

        return new JsonModel($result);
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
        if ($photo) {
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
