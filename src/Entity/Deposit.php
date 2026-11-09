<?php

namespace App\Entity;

use App\Entity\Trait\OwnerTrait;
use App\Repository\DepositRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DepositRepository::class)]
class Deposit implements OwnedInterface
{
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $depositedAt;

    #[ORM\ManyToOne(targetEntity: DepositType::class, inversedBy: 'deposits')]
    #[ORM\JoinColumn(nullable: false)]
    private $type;

    #[ORM\Column(type: 'float')]
    private $amount;

    #[ORM\ManyToOne(targetEntity: Exchange::class, inversedBy: 'deposits')]
    #[ORM\JoinColumn(nullable: false)]
    private $exchange;

    #[ORM\ManyToOne(inversedBy: 'deposits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FiatCurrency $fiatCurrency = null;

    public function __construct(User $owner)
    {
        $this->owner = $owner;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepositedAt(): ?\DateTimeImmutable
    {
        return $this->depositedAt;
    }

    public function setDepositedAt(?\DateTimeImmutable $depositedAt): self
    {
        $this->depositedAt = $depositedAt;

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

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getAmountUsd(): ?float
    {
        return $this->fiatCurrency->getFixerKey() == FiatCurrency::$KEY_USD ? $this->amount : $this->amount * $this->fiatCurrency->getRates()[FiatCurrency::$KEY_USD];
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

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

    public function getFiatCurrency(): ?FiatCurrency
    {
        return $this->fiatCurrency;
    }

    public function setFiatCurrency(?FiatCurrency $fiatCurrency): self
    {
        $this->fiatCurrency = $fiatCurrency;

        return $this;
    }
}
