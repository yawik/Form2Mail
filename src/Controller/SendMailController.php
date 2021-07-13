<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Core\EventManager\EventManager;
use Form2Mail\Controller\Plugin\SendMail;
use Form2Mail\Controller\Plugin\StoreApplication;
use Form2Mail\Options\ModuleOptions;
use Form2Mail\Options\SendmailOrganizationOptionsCollection;
use Jobs\Entity\JobInterface;
use Jobs\Entity\StatusInterface;
use Jobs\Listener\Events\JobEvent;
use Jobs\Repository\Job as JobsRepository;
use Organizations\Repository\Organization as OrganizationRepository;
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
    const ERROR_NO_ENTITY = 'NO_ENTITY';
    const ERROR_INVALID_JSON = 'INVALID_JSON';
    const ERROR_MAIL_FAILED = 'MAIL_FAILED';
    const ERROR_STORE_FAILED = 'STORE_FAILED';

    protected static $errors = [
        self::ERROR_NO_POST => 'Must use POST request',
        self::ERROR_INVALID_JSON => 'Invalid json',
        self::ERROR_NO_REF => 'Missing job or organization reference',
        self::ERROR_NO_ENTITY => 'No job or organization found',
        self::ERROR_MAIL_FAILED => 'Sending of mail failed',
        self::ERROR_STORE_FAILED => 'Storing application failed',
    ];

    private $jobs;
    private $jobEvents;
    private $orgs;
    private $organizationOptions;
    private $moduleOptions;

    public function __construct(
        JobsRepository $jobs,
        EventManager $jobEvents,
        OrganizationRepository $orgs
    ) {
        $this->jobs = $jobs;
        $this->jobEvvents = $jobEvents;
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

    /**
     * Get moduleoptions
     *
     * @return ModuleOptions
     */
    public function getModuleoptions(): ModuleOptions
    {
        if (!$this->moduleOptions) {
            $this->setModuleOptions(new ModuleOptions());
        }

        return $this->moduleOptions;
    }

    /**
     * Set moduleoptions
     *
     * @param ModuleOptions $moduleoptions
     */
    public function setModuleoOtions(ModuleOptions $moduleOptions): void
    {
        $this->moduleOptions = $moduleOptions;
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
            $job = $this->createInitialJob($org);
        } else {
            $org = $job->getOrganization();
        }

        $orgOptions = $this->getOrganizationOptions()->getOrganizationOptions($job->getOrganization()->getId());
        $moduleOptions = $this->getModuleoptions();
        $files = $this->getRequest()->getFiles()->toArray();

        if ($moduleOptions->doStoreApplications() || $orgOptions->doStoreApplications()) {
            $plugin = $this->plugin(StoreApplication::class);
        } else {
            $plugin = $this->plugin(SendMail::class);
        }

        try {
            $result = $plugin($json, $files, $job, $org, $orgOptions, $moduleOptions);
        } catch (\Laminas\Mail\Exception\ExceptionInterface $e) {
            /** @var \Throwable $e */
            return $this->createErrorModel(
                $plugin instanceof SendMail ? self::ERROR_MAIL_FAILED : self::ERROR_STORE_FAILED,
                Response::STATUS_CODE_500,
                ['error' => $e->getMessage()]
            );
        }

        return new JsonModel($result);
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

    private function createInitialJob($org)
    {
        $options = $this->getModuleoptions();
        $jobTitle = $options->getInitialApplicationJobTitle() ?? 'Initial application';
        $job = $this->jobs->findOneBy(['title' => $jobTitle, 'organization' => $org]);

        if ($job) {
            return $job;
        }

        $job = $this->jobs->create([
            'title' => $options->getInitialApplicationJobTitle() ?? 'Initial application',
            'organization' => $org,
            'user' => $org->getUser(),
        ]);
        $job->setReference($this->jobs->getUniqueReference());
        $job->changeStatus(StatusInterface::CREATED, "job was created by " . get_class($this));
        $job->setAtsEnabled(true);

        // sets ATS-Mode on intern
        $job->getAtsMode();

        $this->jobEvents->trigger(JobEvent::EVENT_JOB_CREATED, $this, array('job' => $job));
        return $job;
    }
}
