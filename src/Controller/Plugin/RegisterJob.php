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

    public function __invoke(array $spec, bool $allowMultiple = false)
    {
        extract([
            'userEmail' => $spec['user']['email'] ?? '',
            'userName' => $spec['user']['name'] ?? '',
            'orgName' => $spec['org']['name'] ?? $spec['user']['email'] ?? '',
            'jobUri' => $spec['job']['uri'] ?? '',
            'jobTitle' => $spec['job']['title'] ?? '',
        ]);

        if ($allowMultiple && ($user = $this->users->findByLoginOrEmail($userEmail))) {
            $organization = $user->getOrganization()->getOrganization();
        } else {
            $user = $this->createUser($userEmail, $userName);
            $organization = $this->createOrganization($user, $orgName);
        }
        $job = $this->createJob($user, $organization, $jobUri, $jobTitle);
        $meta = $this->createUserMeta($user, $job);

        $dm = $this->users->getDocumentManager();
        $dm->persist($user);
        $dm->persist($organization);
        $dm->persist($job);
        $dm->persist($meta);
        $dm->flush();

        return $job;
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

    private function createUserMeta(UserInterface $user, JobInterface $job, string $type = UserMetaData::TYPE_INVITED)
    {
        $meta = $this->meta->findOrCreateMetaData($user, $job);
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

    protected function createJob($user, $organization, $uri, $title)
    {
        /** @var \Jobs\Entity\Job $job */
        $job = $this->jobs->create();
        $job->setOrganization($organization);
        $job->setUser($user);
        $job->setLink($uri);
        $job->setTitle($title);

        return $job;
    }
}
