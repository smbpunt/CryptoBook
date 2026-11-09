<?php

namespace App\Entity\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * The user who owns the data. Set once in the constructor, never changed.
 */
trait OwnerTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    public function getOwner(): User
    {
        return $this->owner;
    }
}
