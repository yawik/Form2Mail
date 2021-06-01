<?php

/**
 * AMS Form2Mail
 * * @copyright 2013-2021 CROSS Solution

 */

declare(strict_types=1);

namespace Form2Mail\Entity;

use Auth\Entity\UserInterface;
use Core\Entity\EntityInterface;
use Core\Entity\EntityTrait;
use Core\Entity\IdentifiableEntityInterface;
use Core\Entity\IdentifiableEntityTrait;
use Core\Entity\MetaDataProviderInterface;
use Core\Entity\MetaDataProviderTrait;
use Core\Exception\ImmutablePropertyException;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * TODO: description
 *
 * @author Mathias Gelhausen
 * TODO: write tests
 *
 * @ODM\Document(repositoryClass="\Form2Mail\Repository\UserMetaDataRepository")
 */
class UserMetaData implements EntityInterface, IdentifiableEntityInterface
{
    use EntityTrait, IdentifiableEntityTrait;

    const TYPE_INVITED = 'INVITED';
    const TYPE_REGISTERED = 'REGISTERED';

    const STATE_NEW = 'NEW';
    const STATE_PENDING = 'PENDING';
    const STATE_CONFIRMED = 'CONFIRMED';

    /**
     * @var \Auth\Entity\UserInterface
     * @ODM\ReferenceOne(targetDocument="\Auth\Entity\User")
     * */
    private $user;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $state = self::STATE_NEW;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    private $type = self::TYPE_REGISTERED;

    /**
     * Get user
     *
     * @return \Auth\Entity\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param \Auth\Entity\UserInterface $user
     */
    public function setUser(UserInterface $user): void
    {
        if ($this->user) {
            throw new ImmutablePropertyException('user', $this);
        }

        $this->user = $user;
    }

    public function setState(string $state): void
    {
        $validStates = [
            '::STATE_NEW' => self::STATE_NEW,
            '::STATE_CONFIRMED' => self::STATE_CONFIRMED,
            '::STATE::PENDING' => self::STATE_PENDING,
        ];

        if (!in_array($state, $validStates)) {
            throw new \OutOfBoundsException(sprintf(
                'State must be one of: %1$s' . join(', %1$s', array_keys($validStates)),
                get_class($this)
            ));
        }

        $this->state = $state;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function isState(string $state): bool
    {
        return $this->state === $state;
    }

    public function setType(string $type): void
    {
        if ($this->state) {
            throw new ImmutablePropertyException('type', $this);
        }

        if ($type !== self::TYPE_REGISTERED && $type !== self::TYPE_INVITED) {
            throw new \OutOfBoundsException(sprintf(
                'Type must be either %1$s::REGISTERED or %1$s::INVITED',
                get_class($this)
            ));
        }

        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }
}
