<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Core\Mail\MailService;
use Form2Mail\Options\SendmailOrganizationOptions;
use Form2Mail\Options\SendmailOrganizationOptionsCollection;
use Jobs\Repository\Job as JobsRepository;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

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
    const ERROR_NO_JOB = 'NO_JOB';
    const ERROR_INVALID_JSON = 'INVALID_JSON';
    const ERROR_MAIL_FAILED = 'MAIL_FAILED';

    private static $errors = [
        self::ERROR_NO_POST => 'Must use POST request',
        self::ERROR_INVALID_JSON => 'Invalid json',
        self::ERROR_NO_REF => 'Missing job reference',
        self::ERROR_NO_JOB => 'No job found',
        self::MAIl_FAILED => 'Sending of mail failed',
    ];

    private $mails;
    private $jobs;
    private $organizationOptions;

    public function __construct(MailService $mails, JobsRepository $jobs)
    {
        $this->mails = $mails;
        $this->jobs = $jobs;
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

        $data = $this->getRequest()->getContent();
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

        $applyId = $json['ref'] ?? $this->params()->fromQuery('ref');

        if (!$applyId) {
            return $this->createErrorModel(self::ERROR_NO_REF, Response::STATUS_CODE_400);
        }

        $job = $this->jobs->findOneBy(['applyId' => $applyId]) ?? $this->jobs->findOneBy(['id' => $applyId]);

        if (!$job) {
            return $this->createErrorModel(
                self::ERROR_NO_JOB,
                Response::STATUS_CODE_400,
                ['ref' => $applyId]
            );
        }

        //$options = $this->getOrganizationOptions()->getOrganizationOptions($job->getOrganization()->getId());

        $mail = $this->mails->get('htmltemplate');
        $mail->setTemplate('form2mail/sendmail');
        $mail->setVariables($json);
        $mail->setSubject(sprintf('Bewerbung auf %s', $job->getTitle()));
        $mail->addTo($job->getOrganization()->getUser()->getInfo()->getEmail());

        try {
            $this->mails->send($mail);
        } catch (\Laminas\Mail\Exception\ExceptionInterface $e) {
            /** @var \Throwable $e */
            return $this->createErrorModel(self::ERROR_MAIL_FAILED, Response::STATUS_CODE_500, ['error' => $e->getMessage()]);
        }

        return new JsonModel([
            'success' => true,
            'message' => 'Mail send successfully',
            'payload' => $json
        ]);
    }

    private function createErrorModel(string $type, $code = null, ?array $extras = null)
    {
        $this->getResponse()->setStatusCode($code ?? Response::STATUS_CODE_500);

        $result = [
            'success' => false,
            'message' => self::$errors[$type] ?? 'An unknown error occured.',
        ];

        if ($extras) {
            $result['extras'] = $extras;
        }

        return new JsonModel($result);
    }
}
