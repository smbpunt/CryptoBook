<?php

namespace App\Strategy\Farming\Entity;

use App\Blockchain\Entity\Blockchain;
use App\Blockchain\Entity\Dapp;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Shared\Entity\DescriptionTrait;
use App\Strategy\Farming\Repository\StrategyFarmingRepository;
use App\User\Entity\OwnedInterface;
use App\User\Entity\OwnerTrait;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StrategyFarmingRepository::class)]
class StrategyFarming implements OwnedInterface
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
    private ?Cryptocurrency $coin = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    private ?float $nbCoins = null;

    #[ORM\ManyToOne(targetEntity: Dapp::class)]
    private ?Dapp $dapp = null;

    private ?Blockchain $blockchain = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    private ?float $apr = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $enteredAt = null;

    public function __construct(User $owner)
    {
        $this->owner = $owner;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoin(): ?Cryptocurrency
    {
        return $this->coin;
    }

    public function setCoin(?Cryptocurrency $coin): self
    {
        $this->coin = $coin;

        return $this;
    }

    public function getNbCoins(): ?float
    {
        return $this->nbCoins;
    }

    public function setNbCoins(?float $nbCoins): self
    {
        $this->nbCoins = $nbCoins;

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

    public function getApr(): ?float
    {
        return $this->apr;
    }

    public function setApr(?float $apr): self
    {
        $this->apr = $apr;

        return $this;
    }

    public function getEnteredAt(): ?\DateTimeImmutable
    {
        return $this->enteredAt;
    }

    public function setEnteredAt(?\DateTimeImmutable $enteredAt): self
    {
        $this->enteredAt = $enteredAt;

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

    public function getValueUsd(): float
    {
        return $this->nbCoins * $this->coin->getPriceUsd();
    }

    public function getAnnualRewardsUsd(): float
    {
        return $this->apr * $this->getValueUsd() / 100;
    }
}
