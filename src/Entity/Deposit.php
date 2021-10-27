<?php

namespace App\Entity;

use App\Repository\DepositRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DepositRepository::class)
 */
class Deposit
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $depositedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Exchange::class, inversedBy="deposits")
     */
    private $exchange;

    /**
     * @ORM\ManyToOne(targetEntity=DepositType::class, inversedBy="deposits")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="deposits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="float")
     */
    private $valueEur;

    /**
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepositedAt(): ?DateTimeImmutable
    {
        return $this->depositedAt;
    }

    public function setDepositedAt(?DateTimeImmutable $depositedAt): self
    {
        $this->depositedAt = $depositedAt;

        return $this;
    }

    public function getExchange(): ?Exchange
    {
        return $this->exchange;
    }

    public function setExchange(?Exchange $exchange): self
    {
        $this->exchange = $exchange;

        return $this;
    }

    public function getType(): ?DepositType
    {
        return $this->type;
    }

    public function setType(?DepositType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getValueEur(): ?float
    {
        return $this->valueEur;
    }

    public function setValueEur(float $valueEur): self
    {
        $this->valueEur = $valueEur;

        return $this;
    }
}
