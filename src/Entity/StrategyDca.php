<?php

namespace App\Entity;

use App\Repository\StrategyDcaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StrategyDcaRepository::class)]
class StrategyDca
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(inversedBy: 'strategyDca', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    #[ORM\OneToMany(mappedBy: 'strategyDca', targetEntity: CoinPercentDca::class, cascade: ['persist'], orphanRemoval: true)]
    private $parts;

    #[ORM\Column(type: 'float')]
    private $fiatToDcaEur;

    #[ORM\Column(type: 'float')]
    private $farmingToDcaUsd;

    public function __construct($owner)
    {
        $this->owner = $owner;
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
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
