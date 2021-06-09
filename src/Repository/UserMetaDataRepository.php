<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Repository;

use Auth\Entity\UserInterface;
use Core\Repository\AbstractRepository;
use Form2Mail\Entity\UserMetaData;
use Jobs\Entity\JobInterface;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class UserMetaDataRepository extends AbstractRepository
{
    public function findOrCreateMetaData(UserInterface $user, JobInterface $job): UserMetaData
    {
        $entity = $this->findOneBy(['user' => $user->getId(), 'job' => $job->getId()]);

        if (!$entity) {
            $entity = $this->create(['user' => $user, 'job' => $job], true);
        }

        return $entity;
    }

    public function findMetaDataOfInvitableUsers()
    {
        $criteria = [
            'type' => UserMetaData::TYPE_INVITED,
            'state' => UserMetaData::STATE_NEW,
        ];

        return $this->findBy($criteria);
    }
}
