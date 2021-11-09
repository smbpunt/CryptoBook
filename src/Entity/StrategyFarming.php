<?php

namespace App\Entity;

use App\Repository\StrategyFarmingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StrategyFarmingRepository::class)
 */
class StrategyFarming
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="farmingStrategies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coin;

    /**
     * @ORM\Column(type="float")
     */
    private $nbCoins;

    /**
     * @var Blockchain
     */
    private $blockchain;

    /**
     * @ORM\ManyToOne(targetEntity=Dapp::class, inversedBy="farmingStrategies")
     */
    private $dapp;

    /**
     * @ORM\Column(type="float")
     */
    private $apr;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="farmingStrategies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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

    public function getBlockchain(): ?Blockchain
    {
        if ($this->blockchain === null && $this->dapp !== null && $this->dapp->getBlockchain() !== $this->blockchain) {
            $this->blockchain = $this->dapp->getBlockchain();
        }
        return $this->blockchain;
    }

    public function setBlockchain(?Blockchain $blockchain): self
    {
        $this->blockchain = $blockchain;

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
