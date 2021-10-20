<?php

namespace App\Entity;

use App\Repository\PositionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PositionRepository::class)
 */
class Position
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $openedAt;

    /**
     * @ORM\Column(type="float")
     */
    private $nbCoins;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coin;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOpened;

    /**
     * @ORM\Column(type="float")
     */
    private $entryCost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?\DateTimeImmutable $openedAt): self
    {
        $this->openedAt = $openedAt;

        return $this;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIsOpened(): ?bool
    {
        return $this->isOpened;
    }

    public function setIsOpened(bool $isOpened): self
    {
        $this->isOpened = $isOpened;

        return $this;
    }

    public function getEntryCoinValue(): ?float
    {
        return round(($this->getEntryCost() / $this->getNbCoins()), 2);
    }

    public function getEntryCost(): ?float
    {
        return $this->entryCost;
    }

    public function setEntryCost(float $entryCost): self
    {
        $this->entryCost = $entryCost;

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

}
