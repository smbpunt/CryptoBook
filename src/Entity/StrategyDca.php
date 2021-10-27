<?php

namespace App\Entity;

use App\Repository\StrategyDcaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StrategyDcaRepository::class)
 */
class StrategyDca
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="strategyDca", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=CoinPercentDca::class, mappedBy="strategyDca", orphanRemoval=true)
     */
    private $parts;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|CoinPercentDca[]
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
}
