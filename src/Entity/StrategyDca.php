<?php

namespace App\Entity;

use App\Repository\StrategyDcaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StrategyDcaRepository::class)]
class StrategyDca implements OwnedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[ORM\OneToMany(mappedBy: 'strategyDca', targetEntity: CoinPercentDca::class, cascade: ['persist'], orphanRemoval: true)]
    private $parts;

    #[ORM\Column(type: 'float')]
    private $fiatToDcaEur;

    #[ORM\Column(type: 'float')]
    private $farmingToDcaUsd;

    public function __construct(User $owner)
    {
        $this->owner = $owner;
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @return Collection<int, CoinPercentDca>
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(CoinPercentDca $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts[] = $part;
            $part->setStrategyDca($this);
        }

        return $this;
    }

    public function removePart(CoinPercentDca $part): self
    {
        if ($this->parts->removeElement($part)) {
            // set the owning side to null (unless already changed)
            if ($part->getStrategyDca() === $this) {
                $part->setStrategyDca(null);
            }
        }

        return $this;
    }

    public function getFiatToDcaEur(): ?float
    {
        return $this->fiatToDcaEur;
    }

    public function setFiatToDcaEur(float $fiatToDcaEur): self
    {
        $this->fiatToDcaEur = $fiatToDcaEur;

        return $this;
    }

    public function getFarmingToDcaUsd(): ?float
    {
        return $this->farmingToDcaUsd;
    }

    public function setFarmingToDcaUsd(float $farmingToDcaUsd): self
    {
        $this->farmingToDcaUsd = $farmingToDcaUsd;

        return $this;
    }
}
