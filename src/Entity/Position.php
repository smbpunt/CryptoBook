<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Trait\DescriptionTrait;
use App\Repository\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    collectionOperations: ['GET', 'POST'],
    itemOperations: ['GET', 'PUT', 'DELETE'],
    denormalizationContext: ['disable_type_enforcement' => true, 'groups' => ['position:write']],
    normalizationContext: ['groups' => ['position:list', 'position:item']]
)]
class Position
{
    use DescriptionTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['position:list', 'position:item'])]
    private $id;

    #[ORM\Column(type: 'float')]
    #[Groups(['position:list', 'position:item'])]
    private $nbCoins;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class, inversedBy: 'positions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['position:list', 'position:item'])]
    private $coin;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'positions')]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['position:list', 'position:item'])]
    private $isOpened;

    #[ORM\Column(type: 'float')]
    #[Groups(['position:list', 'position:item'])]
    private $entryCost;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['position:list', 'position:item'])]
    private $openedAt;

    #[ORM\Column(type: 'float')]
    #[Groups(['position:list', 'position:item'])]
    private $remainingCoins;

    #[ORM\Column(type: 'boolean')]
    private $isDca;

    #[ORM\OneToMany(mappedBy: 'position', targetEntity: Vente::class, cascade: ['persist'], orphanRemoval: true)]
    private $ventes;

    #[ORM\OneToMany(mappedBy: 'position', targetEntity: StrategyPosition::class, cascade: ['persist'], orphanRemoval: true)]
    private $strategies;

    public function __construct($owner)
    {
        $this->ventes = new ArrayCollection();
        $this->strategies = new ArrayCollection();
        $this->owner = $owner;
        $this->isOpened = true;
        $this->isDca = false;
        $this->remainingCoins = 0;
        $this->description = "";
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCoin(): ?Cryptocurrency
    {
        return $this->coin;
    }

    public function setCoin(?Cryptocurrency $coin): self
    {
        $this->coin = $coin;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function isIsOpened(): ?bool
    {
        return $this->isOpened;
    }

    public function setIsOpened(bool $isOpened): self
    {
        $this->isOpened = $isOpened;

        return $this;
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

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt(?\DateTimeImmutable $openedAt): self
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

    public function isIsDca(): ?bool
    {
        return $this->isDca;
    }

    public function setIsDca(bool $isDca): self
    {
        $this->isDca = $isDca;

        return $this;
    }

    /**
     * @return Collection<int, Vente>
     */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    public function addVente(Vente $vente): self
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes[] = $vente;
            $vente->setPosition($this);
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
     * @return Collection<int, StrategyPosition>
     */
    public function getStrategies(): Collection
    {
        return $this->strategies;
    }

    public function addStrategy(StrategyPosition $strategy): self
    {
        if (!$this->strategies->contains($strategy)) {
            $this->strategies[] = $strategy;
            $strategy->setPosition($this);
        }

        return $this;
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

    public function getEntryCoinValue(): ?float
    {
        return round(($this->getEntryCost() / $this->getNbCoins()), 2);
    }

    #[Groups(['position:item', 'position:list'])]
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
        return ($this->entryCost > 0 && $this->getPercentRemainingCoins() > 0) //Pour la division par 0
            ? (($this->getCurrentValue() - ($this->entryCost * $this->getPercentRemainingCoins())) / ($this->entryCost * $this->getPercentRemainingCoins())) * 100
            : 9999;
    }

    /**
     * @return Collection
     */
    public function getVentesSortedByDate(): Collection
    {
        return $this->ventes->matching(new Criteria(null, ['soldAt' => Criteria::ASC]));
    }

    #[ORM\PreFlush]
    public function calculateRemainingCoins(): void
    {
        $this->remainingCoins = $this->nbCoins;
        if ($this->ventes->isEmpty()) {
            return;
        }

        $ventes = $this->getVentesSortedByDate();
        foreach ($ventes as $vente) {
            $this->remainingCoins *= (1 - ($vente->getPercent() / 100));
        }

        if ($this->remainingCoins === 0.0) {
            $this->setIsOpened(false);
        }
    }
}
