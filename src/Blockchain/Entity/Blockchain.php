<?php

namespace App\Blockchain\Entity;

use App\Blockchain\Repository\BlockchainRepository;
use App\Cryptocurrency\Entity\Cryptocurrency;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BlockchainRepository::class)]
#[UniqueEntity('libelle')]
class Blockchain implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Cryptocurrency $coin = null;

    /** @var Collection<int, Dapp> */
    #[ORM\OneToMany(mappedBy: 'blockchain', targetEntity: Dapp::class, orphanRemoval: true)]
    private Collection $dapps;

    public function __construct()
    {
        $this->dapps = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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

    /**
     * @return Collection<int, Dapp>
     */
    public function getDapps(): Collection
    {
        return $this->dapps;
    }

    public function addDapp(Dapp $dapp): self
    {
        if (!$this->dapps->contains($dapp)) {
            $this->dapps[] = $dapp;
            $dapp->setBlockchain($this);
        }

        return $this;
    }

    public function removeDapp(Dapp $dapp): self
    {
        if ($this->dapps->removeElement($dapp)) {
            // set the owning side to null (unless already changed)
            if ($dapp->getBlockchain() === $this) {
                $dapp->setBlockchain(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->libelle;
    }
}
