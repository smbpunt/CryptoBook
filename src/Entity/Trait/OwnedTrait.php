<?php

namespace App\Entity\Trait;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait OwnedTrait {
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'positions')]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}