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
    public function findOrCreateMetaData(UserInterface $user): UserMetaData
    {
        $entity = $this->findOneBy(['user' => $user->getId()]);

        if (!$entity) {
            $entity = $this->create(['user' => $user], true);
        }

        return $entity;
    }
}
