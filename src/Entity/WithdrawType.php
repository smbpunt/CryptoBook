<?php

namespace App\Entity;

use App\Repository\WithdrawTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WithdrawTypeRepository::class)]
class WithdrawType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Withdraw>
     */
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Withdraw::class)]
    private Collection $withdraws;

    public function __construct()
    {
        $this->withdraws = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Withdraw>
     */
    public function getWithdraws(): Collection
    {
        return $this->withdraws;
    }

    public function addWithdraw(Withdraw $withdraw): static
    {
        if (!$this->withdraws->contains($withdraw)) {
            $this->withdraws->add($withdraw);
            $withdraw->setType($this);
        }

        return $this;
    }

    public function removeWithdraw(Withdraw $withdraw): static
    {
        if ($this->withdraws->removeElement($withdraw)) {
            // set the owning side to null (unless already changed)
            if ($withdraw->getType() === $this) {
                $withdraw->setType(null);
            }
        }

        return $this;
    }
}
