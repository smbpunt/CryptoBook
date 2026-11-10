<?php

namespace App\Position\Entity;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Position\Repository\PositionRepository;
use App\Shared\Entity\DescriptionTrait;
use App\User\Entity\OwnedInterface;
use App\User\Entity\OwnerTrait;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PositionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Position implements OwnedInterface
{
    use DescriptionTrait;
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\Positive]
    private $nbCoins;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private $coin;

    #[ORM\Column(type: 'boolean')]
    private $isOpened;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private $entryCost;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $openedAt;

    #[ORM\Column(type: 'float')]
    private $remainingCoins;

    #[ORM\Column(type: 'boolean')]
    private $isDca;

    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'position', targetEntity: Vente::class, cascade: ['persist'], orphanRemoval: true)]
    private $ventes;

    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'position', targetEntity: StrategyPosition::class, cascade: ['persist'], orphanRemoval: true)]
    private $strategies;

    public function __construct(User $owner)
    {
        $this->ventes = new ArrayCollection();
        $this->strategies = new ArrayCollection();
        $this->owner = $owner;
        $this->isOpened = true;
        $this->isDca = false;
        $this->description = '';
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCoin(): ?Cryptocurrency
    {
        return $this->coin;
    }

    public function setCoin(?Cryptocurrency $coin): self
    {
        $this->coin = $coin;

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

    public function setEntryCost(?float $entryCost): self
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
        return round($this->getEntryCost() / $this->getNbCoins(), 2);
    }

    public function getCurrentValue(): float
    {
        return $this->remainingCoins * $this->coin->getPriceUsd();
    }

    public function getPercentRemainingCoins(): float
    {
        return $this->remainingCoins / $this->nbCoins;
    }

    /**
     * Evolution of the value of the coins left against their cost, null when they cost nothing.
     */
    public function getPercentEvolution(): ?float
    {
        $cost = $this->entryCost * $this->getPercentRemainingCoins();
        if ($cost <= 0) {
            return null;
        }

        return ($this->getCurrentValue() - $cost) / $cost * 100;
    }

    /**
     * @return list<Vente>
     */
    public function getVentesSortedByDate(): array
    {
        return array_values($this->ventes->matching(new Criteria(orderings: ['soldAt' => \SortDirection::Ascending]))->toArray());
    }

    #[ORM\PreFlush]
    public function calculateRemainingCoins(): void
    {
        $previous = $this->remainingCoins;
        $this->remainingCoins = $this->nbCoins;
        foreach ($this->getVentesSortedByDate() as $vente) {
            $this->remainingCoins *= (1 - ($vente->getPercent() / 100));
        }

        // Sold out: closed. Coins back after a sale was lowered or removed: opened again.
        // A position closed by hand with coins left stays closed (a new one has no previous count).
        if (0.0 === $this->remainingCoins) {
            $this->setIsOpened(false);
        } elseif (0.0 === $previous) {
            $this->setIsOpened(true);
        }
    }
}
