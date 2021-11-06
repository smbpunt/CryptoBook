<?php

namespace App\Entity;

use App\Repository\StrategyLPRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StrategyLPRepository::class)
 */
class StrategyLP
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="strategyLp1s")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coin1;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="strategyLp2s")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coin2;

    /**
     * @ORM\ManyToOne(targetEntity=Dapp::class, inversedBy="strategyLPs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dapp;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $startAt;

    /**
     * @ORM\Column(type="float")
     */
    private $priceCoin1;

    /**
     * @ORM\Column(type="float")
     */
    private $priceCoin2;

    /**
     * @ORM\Column(type="float")
     */
    private $nbCoin1;

    /**
     * @ORM\Column(type="float")
     */
    private $nbCoin2;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $lpDeposit;

    /**
     * @ORM\Column(type="float")
     */
    private $apr;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="strategyLPs")
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

    public function setPriceCoin1(float $priceCoin1): self
    {
        $this->priceCoin1 = $priceCoin1;

        return $this;
    }

    public function getPriceCoin2(): ?float
    {
        return $this->priceCoin2;
    }

    public function setPriceCoin2(float $priceCoin2): self
    {
        $this->priceCoin2 = $priceCoin2;

        return $this;
    }

    public function getNbCoin1(): ?float
    {
        return $this->nbCoin1;
    }

    public function setNbCoin1(float $nbCoin1): self
    {
        $this->nbCoin1 = $nbCoin1;

        return $this;
    }

    public function getNbCoin2(): ?float
    {
        return $this->nbCoin2;
    }

    public function setNbCoin2(float $nbCoin2): self
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
