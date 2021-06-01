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
use Form2Mail\Entity\UserMetaData;
use Form2Mail\Repository\UserMetaDataRepository;
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

    private $users;
    private $organizations;
    private $jobs;
    private $jobUrl;
    /** @var UserMetaDataRepository */
    private $meta;

    public function __construct(
        UserRepository $users,
        OrganizationRepository $organizations,
        JobRepository $jobs,
        UserMetaDataRepository $meta,
        JobUrl $jobUrl
    ) {
        $this->users = $users;
        $this->organizations = $organizations;
        $this->jobs = $jobs;
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

        if (!$email || !$uri) {
            return $this->createErrorModel('Missing email or job uri.', Response::STATUS_CODE_400);
        }

        try {
            $user = $this->createUser($email, $this->params()->fromPost('name', ''));
            $organization = $this->createOrganization($user, $this->params()->fromPost('organization'));
            $job = $this->createJob($user, $organization, $uri);
            $meta = $this->createUserMeta($user);

            $dm = $this->users->getDocumentManager();
            $dm->persist($user);
            $dm->persist($organization);
            $dm->persist($job);
            $dm->persist($meta);
            $dm->flush();
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

    private function createUser(string $email, string $name = '')
    {
        $role = User::ROLE_RECRUITER;

        if (($this->users->findByLoginOrEmail($email))) {
            throw new \UnexpectedValueException('User already exists');
        }

        $user = $this->users->create([
            'login' => $email,
            'role' => $role,
        ]);

        $info = $user->getInfo();
        $info->setEmail($email);
        $info->setFirstName($name);
        $info->setEmailVerified(true);

        if (strstr($name, ' ') !== false) {
            $nameParts = explode(' ', $name);
            $firstName = array_shift($nameParts);
            $lastName = implode(' ', $nameParts);

            $info->setFirstName($firstName);
            $info->setLastName($lastName);
        }

        $user->setPassword(uniqid('credentials', true));
        return $user;
    }

    private function createUserMeta(UserInterface $user, string $type = UserMetaData::TYPE_INVITED)
    {
        $meta = $this->meta->findOrCreateMetaData($user);
        $meta->setState(UserMetaData::STATE_NEW);
        $meta->setType($type);

        return $meta;
    }

    private function createOrganization($user, ?string $name = null)
    {
        $organization = $this->organizations->createWithName(
            $name ?? $user->getLogin()
        );
        $organization->setUser($user);
        $organization->getPermissions()->grant($user, PermissionsInterface::PERMISSION_ALL);

        return $organization;
    }

    protected function createJob($user, $organization, $uri)
    {
        /** @var \Jobs\Entity\Job $job */
        $job = $this->jobs->create();
        $job->setOrganization($organization);
        $job->setUser($user);
        $job->setLink($uri);

        return $job;
    }
}
