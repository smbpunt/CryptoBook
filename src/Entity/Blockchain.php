<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BlockchainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BlockchainRepository::class)]
#[ApiResource(
    collectionOperations: ['GET'],
    itemOperations: ['GET'],
    normalizationContext: ['groups' => ['loan:list', 'loan:item']]
)]
class Blockchain
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $libelle;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class, inversedBy: 'blockchains')]
    #[ORM\JoinColumn(nullable: false)]
    private $coin;

    #[ORM\OneToMany(mappedBy: 'blockchain', targetEntity: Dapp::class, orphanRemoval: true)]
    private $dapps;

    #[ORM\OneToMany(mappedBy: 'blockchain', targetEntity: Nft::class)]
    private $nfts;

    public function __construct()
    {
        $this->dapps = new ArrayCollection();
        $this->nfts = new ArrayCollection();
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

    /**
     * @return Collection<int, Nft>
     */
    public function getNfts(): Collection
    {
        return $this->nfts;
    }

    public function addNft(Nft $nft): self
    {
        if (!$this->nfts->contains($nft)) {
            $this->nfts[] = $nft;
            $nft->setBlockchain($this);
        }

        return $this;
    }

    public function removeNft(Nft $nft): self
    {
        if ($this->nfts->removeElement($nft)) {
            // set the owning side to null (unless already changed)
            if ($nft->getBlockchain() === $this) {
                $nft->setBlockchain(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->libelle;
    }
}
