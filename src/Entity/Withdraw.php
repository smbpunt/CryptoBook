<?php

namespace App\Entity;

use App\Repository\WithdrawRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WithdrawRepository::class)]
class Withdraw
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'withdraws')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $withdrawedAt = null;

    #[ORM\ManyToOne(inversedBy: 'withdraws')]
    #[ORM\JoinColumn(nullable: false)]
    private ?WithdrawType $type = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\ManyToOne(inversedBy: 'withdraws')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FiatCurrency $fiatCurrency = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getWithdrawedAt(): ?\DateTimeImmutable
    {
        return $this->withdrawedAt;
    }

    public function setWithdrawedAt(?\DateTimeImmutable $withdrawedAt): static
    {
        $this->withdrawedAt = $withdrawedAt;

        return $this;
    }

    public function getType(): ?WithdrawType
    {
        return $this->type;
    }

    public function setType(?WithdrawType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getFiatCurrency(): ?FiatCurrency
    {
        return $this->fiatCurrency;
    }

    public function setFiatCurrency(?FiatCurrency $fiatCurrency): static
    {
        $this->fiatCurrency = $fiatCurrency;

        return $this;
    }
}
