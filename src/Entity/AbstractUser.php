<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class AbstractUser
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="positions")
     */
    protected $user;


    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}