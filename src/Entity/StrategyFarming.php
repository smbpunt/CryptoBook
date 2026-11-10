<?php

namespace App\Entity;

use App\Blockchain\Entity\Blockchain;
use App\Blockchain\Entity\Dapp;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Entity\Trait\DescriptionTrait;
use App\Entity\Trait\OwnerTrait;
use App\Repository\StrategyFarmingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StrategyFarmingRepository::class)]
class StrategyFarming implements OwnedInterface
{
    use DescriptionTrait;
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $coin;

    #[ORM\Column(type: 'float')]
    private $nbCoins;

    #[ORM\ManyToOne(targetEntity: Dapp::class)]
    private $dapp;

    private $blockchain;

    #[ORM\Column(type: 'float')]
    private $apr;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $enteredAt;

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

    public function setNbCoins(float $nbCoins): self
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

    public function setApr(float $apr): self
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
