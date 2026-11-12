<?php

namespace App\Strategy\Lp\Entity;

use App\Blockchain\Entity\Blockchain;
use App\Blockchain\Entity\Dapp;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Shared\Entity\DescriptionTrait;
use App\Strategy\Lp\Repository\StrategyLpRepository;
use App\User\Entity\OwnedInterface;
use App\User\Entity\OwnerTrait;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StrategyLpRepository::class)]
class StrategyLp implements OwnedInterface
{
    use DescriptionTrait;
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Cryptocurrency $coin1 = null;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Cryptocurrency $coin2 = null;

    #[ORM\ManyToOne(targetEntity: Dapp::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Dapp $dapp = null;

    private ?Blockchain $blockchain = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startAt = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    private ?float $priceCoin1 = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    private ?float $priceCoin2 = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    private ?float $nbCoin1 = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    private ?float $nbCoin2 = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $lpDeposit = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    private ?float $apr = null;

    public function __construct(User $owner)
    {
        $this->owner = $owner;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoin1(): ?Cryptocurrency
    {
        return $this->coin1;
    }

    public function setCoin1(?Cryptocurrency $coin1): self
    {
        $this->coin1 = $coin1;

        return $this;
    }

    public function getCoin2(): ?Cryptocurrency
    {
        return $this->coin2;
    }

    public function setCoin2(?Cryptocurrency $coin2): self
    {
        $this->coin2 = $coin2;

        return $this;
    }

    public function getDapp(): ?Dapp
    {
        return $this->dapp;
    }

    public function setDapp(?Dapp $dapp): self
    {
        $this->dapp = $dapp;

        return $this;
    }

    public function getBlockchain(): ?Blockchain
    {
        if (null === $this->blockchain && null !== $this->dapp && $this->dapp->getBlockchain() !== $this->blockchain) {
            $this->blockchain = $this->dapp->getBlockchain();
        }

        return $this->blockchain;
    }

    public function setBlockchain(?Blockchain $blockchain): self
    {
        $this->blockchain = $blockchain;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getPriceCoin1(): ?float
    {
        return $this->priceCoin1;
    }

    public function setPriceCoin1(?float $priceCoin1): self
    {
        $this->priceCoin1 = $priceCoin1;

        return $this;
    }

    public function getPriceCoin2(): ?float
    {
        return $this->priceCoin2;
    }

    public function setPriceCoin2(?float $priceCoin2): self
    {
        $this->priceCoin2 = $priceCoin2;

        return $this;
    }

    public function getNbCoin1(): ?float
    {
        return $this->nbCoin1;
    }

    public function setNbCoin1(?float $nbCoin1): self
    {
        $this->nbCoin1 = $nbCoin1;

        return $this;
    }

    public function getNbCoin2(): ?float
    {
        return $this->nbCoin2;
    }

    public function setNbCoin2(?float $nbCoin2): self
    {
        $this->nbCoin2 = $nbCoin2;

        return $this;
    }

    public function getLpDeposit(): ?float
    {
        return $this->lpDeposit;
    }

    public function setLpDeposit(?float $lpDeposit): self
    {
        $this->lpDeposit = $lpDeposit;

        return $this;
    }

    public function getApr(): ?float
    {
        return $this->apr;
    }

    public function setApr(?float $apr): self
    {
        $this->apr = $apr;

        return $this;
    }

    /**
     * Value of the coins at the prices entered with the LP.
     */
    public function getInvestedUsd(): float
    {
        return $this->priceCoin1 * $this->nbCoin1 + $this->priceCoin2 * $this->nbCoin2;
    }

    /**
     * Value of the coins at their current price.
     */
    public function getValueUsd(): float
    {
        return $this->coin1->getPriceUsd() * $this->nbCoin1 + $this->coin2->getPriceUsd() * $this->nbCoin2;
    }

    public function getAnnualRewardsUsd(): float
    {
        return $this->getApr() * $this->getValueUsd() / 100;
    }
}
