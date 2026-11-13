<?php

namespace App\Deposit\Entity;

use App\Deposit\Repository\DepositRepository;
use App\Exchange\Entity\Exchange;
use App\FiatCurrency\Entity\FiatCurrency;
use App\FiatCurrency\Exception\MissingRateException;
use App\Shared\Validator\NotInFuture;
use App\User\Entity\OwnedInterface;
use App\User\Entity\OwnerTrait;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DepositRepository::class)]
class Deposit implements OwnedInterface
{
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[NotInFuture]
    private ?\DateTimeImmutable $depositedAt = null;

    #[ORM\ManyToOne(targetEntity: DepositType::class, inversedBy: 'deposits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?DepositType $type = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?float $amount = null;

    #[ORM\ManyToOne(targetEntity: Exchange::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Exchange $exchange = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
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

    /**
     * @throws MissingRateException
     */
    public function getAmountUsd(): float
    {
        return $this->amount * $this->fiatCurrency->rateTo(FiatCurrency::KEY_USD);
    }

    public function setAmount(?float $amount): self
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
