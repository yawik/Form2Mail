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

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 */
class UserMetaDataRepository extends AbstractRepository
{
    public function findOrCreateMetaData(UserInterface $user, string $type = UserMetaData::TYPE_REGISTERED): UserMetaData
    {
        $entity = $this->findOneBy(['user' => $user->getId()]);

        if (!$entity) {
            $entity = $this->create(
                [
                    'user' => $user,
                    'state' => UserMetaData::STATE_NEW,
                    'type' => $type,
                ],
                true
            );
        }

        return $entity;
    }

    public function findMetaDataOfInvitableUsers(?int $limit = null)
    {
        $criteria = [
            'type' => UserMetaData::TYPE_INVITED,
            'state' => UserMetaData::STATE_NEW,
        ];

        if ($limit == 0) {
            /* set 0 => null, to adhere to findBy() signature */
            $limit = null;
        }

        return $this->findBy($criteria, /* sort */ null, /* limit */ $limit);
    }
}
