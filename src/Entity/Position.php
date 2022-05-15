<?php

namespace App\Entity;

use App\Repository\PositionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="float")
     * @Assert\Positive
     */
    private $nbCoins;

    /**
     * @var Cryptocurrency
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
     * @Assert\Positive
     */
    private $entryCost;

    /**
     * @var Vente[]
     * @ORM\OneToMany(targetEntity=Vente::class, mappedBy="position", orphanRemoval=true, cascade={"persist"})
     */
    private $ventes;

    /**
     * @ORM\OneToMany(targetEntity=StrategyPosition::class, mappedBy="position", orphanRemoval=true, cascade={"persist"})
     */
    private $strategies;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $openedAt;

    /**
     * @ORM\Column(type="float")
     */
    private $remainingCoins;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct(UserInterface $user)
    {
        $this->isOpened = true;
        $this->user = $user;
        $this->ventes = new ArrayCollection();
        $this->strategies = new ArrayCollection();
        $this->remainingCoins = 0;
        $this->description = "";
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

    /**
     * @return Collection|Vente[]
     */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    /**
     * @return Collection|Vente[]
     */
    public function getVentesSortedByDate(): Collection
    {
        return $this->ventes->matching(new Criteria(null, ['soldAt' => Criteria::ASC]));
    }

    public function addVente(Vente $vente): self
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes[] = $vente;
            $vente->setPosition($this);
        }

        return $this;
    }

    public function addStrategy(StrategyPosition $strategy): self
    {
        if (!$this->strategies->contains($strategy)) {
            $this->strategies[] = $strategy;
            $strategy->setPosition($this);
        }

        return $this;
    }

    public function removeVente(Vente $vente): self
    {
        if ($this->ventes->removeElement($vente)) {
            // set the owning side to null (unless already changed)
            if ($vente->getPosition() === $this) {
                $vente->setPosition(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StrategyPosition[]
     */
    public function getStrategies(): Collection
    {
        return $this->strategies;
    }

    public function removeStrategy(StrategyPosition $strategy): self
    {
        if ($this->strategies->removeElement($strategy)) {
            // set the owning side to null (unless already changed)
            if ($strategy->getPosition() === $this) {
                $strategy->setPosition(null);
            }
        }

        return $this;
    }

    public function getOpenedAt(): ?DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?DateTimeImmutable $openedAt): self
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getRemainingCoins(): ?float
    {
        return $this->remainingCoins;
    }

    public function setRemainingCoins(float $remainingCoins): self
    {
        $this->remainingCoins = $remainingCoins;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCurrentValue(): float
    {
        return $this->remainingCoins * $this->coin->getPriceUsd();
    }

    public function getPercentRemainingCoins(): float
    {
        return $this->remainingCoins / $this->nbCoins;
    }

    public function getPercentEvolution(): float
    {
        return $this->entryCost > 0
            ? (($this->getCurrentValue() - ($this->entryCost * $this->getPercentRemainingCoins())) / ($this->entryCost * $this->getPercentRemainingCoins())) * 100
            : 9999;
    }

}
