<?php

/**
 * AMS Form2Mail
 *
 * @copyright 2013-2021 CROSS Solution
 */

declare(strict_types=1);

namespace Form2Mail\Controller\Plugin;

use Auth\Entity\User;
use Auth\Entity\UserInterface;
use Core\Entity\PermissionsInterface;
use Auth\Repository\User as UserRepository;
use Form2Mail\Entity\UserMetaData;
use Form2Mail\Repository\UserMetaDataRepository;
use Jobs\Entity\JobInterface;
use Jobs\Entity\StatusInterface as JobStatus;
use Organizations\Repository\Organization as OrganizationRepository;
use Jobs\Repository\Job as JobRepository;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class RegisterJob extends AbstractPlugin
{

    private $users;
    private $organizations;
    private $jobs;
    private $meta;

    public function __construct(
        UserRepository $users,
        OrganizationRepository $organizations,
        JobRepository $jobs,
        UserMetaDataRepository $meta
    ) {
        $this->users = $users;
        $this->organizations = $organizations;
        $this->jobs = $jobs;
        $this->meta = $meta;
    }

    public function __invoke(array $spec, array $options = [])
    {
        extract([
            'userEmail' => $spec['user']['email'] ?? '',
            'userName' => $spec['user']['name'] ?? '',
            'orgName' => $spec['org']['name'] ?? $spec['user']['email'] ?? '',
            'jobUri' => $spec['job']['uri'] ?? '',
            'jobTitle' => $spec['job']['title'] ?? '',
            'metaPortal' => $spec['meta']['portal'] ?? '',
        ]);
        extract(array_merge(
            [
                'allowMultiple' => false,
                'userMetaType' => UserMetaData::TYPE_REGISTERED,
            ],
            $options
        ));

        $result = [
            'user' => null,
            'org' => null,
            'job' => null,
            'meta' => null,
            'flags' => [
                'user_created' => true,
                'job_created' => true,
            ]
        ];

        $result = $this->createUser($result, $userEmail, $userName, $allowMultiple);
        $result = $this->createOrganization($result, $orgName);
        $result = $this->createJob($result, $jobUri, $jobTitle);
        $result = $this->createUserMeta($result, $userMetaType, $metaPortal);

        return $result['job'];
    }

    private function createUser(array $result, string $email, string $name = '', bool $multi = false)
    {
        $role = User::ROLE_RECRUITER;

        if ($user = $this->users->findByLoginOrEmail($email)) {
            if (!$multi) {
                throw new \UnexpectedValueException('User already exists');
            }

            $result['user'] = $user;
            $result['flags']['user_created'] = false;
            return $result;
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

        $this->users->store($user);

        $result['user'] = $user;
        return $result;
    }

    private function createUserMeta(
        array $result,
        ?string $type = null,
        ?string $portal = null
    ) {
        $user = $result['user'];
        $job = $result['job'];
        $meta = $this->meta->findOrCreateMetaData($user, $type);

        if ($result['flags']['job_created']) {
            $meta->addJob($job);
        }
        if ($portal) {
            $meta->addPortal($portal);
        }
        $this->meta->store($meta);
        $result['meta'] = $meta;
        return $result;
    }

    private function createOrganization(array $result, ?string $name = null)
    {
        $user = $result['user'];

        if (!$result['flags']['user_created']) {
            $result['org'] = $user->getOrganization()->getOrganization();
            return $result;
        }

        $organization = $this->organizations->createWithName(
            $name ?? $user->getLogin()
        );
        $organization->setUser($user);
        $organization->getPermissions()->grant($user, PermissionsInterface::PERMISSION_ALL);
        $this->organizations->store($organization);
        $result['org'] = $organization;
        return $result;
    }

    protected function createJob(array $result, $uri, $title)
    {
        $user = $result['user'];
        $organization = $result['org'];

        if ($job = $this->jobs->findOneBy(['link' => $uri])) {
            $result['job'] = $job;
            $result['flags']['job_created'] = false;
            return $result;
        }

        /** @var \Jobs\Entity\Job $job */
        $job = $this->jobs->create();
        $job->setOrganization($organization);
        $job->setUser($user);
        $job->setLink($uri);
        $job->setTitle($title);
        $job->setStatus(JobStatus::ACTIVE);
        $this->jobs->store($job);

        $result['job'] = $job;
        return $result;
    }
}
