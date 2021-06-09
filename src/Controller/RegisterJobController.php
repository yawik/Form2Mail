<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Controller;

use Auth\Entity\User;
use Auth\Entity\UserInterface;
use Auth\Repository\User as UserRepository;
use Core\Entity\PermissionsInterface;
use Form2Mail\Controller\Plugin\RegisterJob;
use Form2Mail\Entity\UserMetaData;
use Form2Mail\Repository\UserMetaDataRepository;
use Jobs\Entity\JobInterface;
use Organizations\Repository\Organization as OrganizationRepository;
use Jobs\Repository\Job as JobRepository;
use Jobs\View\Helper\JobUrl;
use Laminas\Http\Response;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class RegisterJobController extends AbstractApiResponseController
{

    private $jobUrl;

    public function __construct(
        JobUrl $jobUrl
    ) {
        $this->jobUrl = $jobUrl;
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $this->getResponse()->getHeaders()->addHeaderLine('Allow', 'POST');
            return $this->createErrorModel('Must use POST request', Response::STATUS_CODE_405);
        }

        $email = $this->params()->fromPost('email');
        $uri = $this->params()->fromPost('uri');
        $multi = (bool) $this->params()->fromPost('multi');

        if (!$email || !$uri) {
            return $this->createErrorModel('Missing email or job uri.', Response::STATUS_CODE_400);
        }

        try {
            $spec = [
                'user' => [
                    'email' => $email,
                    'name' => $this->params()->fromPost('name'),
                ],
                'org' => ['name' => $this->params()->fromPost('name')],
                'job' => [
                    'uri' => $uri,
                    'title' => $this->params()->fromPost('title'),
                ],
            ];
            $job = ($this->plugin(RegisterJob::class))($spec, $multi);
        } catch (\UnexpectedValueException $e) {
            return $this->createErrorModel(
                'Duplicate email detected',
                Response::STATUS_CODE_400,
                ['message' => $e->getMessage()]
            );
        } catch (\Exception $e) {
            return $this->createErrorModel(
                'Unexpected error',
                ['type' => get_class($e), 'message' => $e->getMessage()]
            );
        }

        $user = $job->getUser();
        $organization = $job->getOrganization();

        return $this->createSuccessModel(
            'Registration successful',
            [
                'login' => $user->getLogin(),
                'name' => $user->getInfo()->getDisplayName(false),
                'organization' => $organization->getName(),
                'job' => ($this->jobUrl)($job, ['linkOnly' => true, 'absolute' => true]),

            ]
        );
    }
}
