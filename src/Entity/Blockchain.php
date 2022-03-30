<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\BlockchainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BlockchainRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"blockchains_read"}},
 * )
 * @UniqueEntity("libelle", message="Une blockchain existe déjà avec ce nom.")
 */
class Blockchain
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"blockchains_read", "dapps_read", "farming_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"blockchains_read", "dapps_read", "farming_read"})
     * @Assert\NotBlank(message="Le libelle est obligatoire.")
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=Dapp::class, mappedBy="blockchain", orphanRemoval=true)
     * @Groups({"blockchains_read"})
     */
    private $dapps;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="blockchains")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"blockchains_read"})
     */
    private $coin;

    /**
     * @ORM\OneToMany(targetEntity=Nft::class, mappedBy="blockchain", orphanRemoval=true)
     */
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

    /**
     * @return Collection|Dapp[]
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

    public function __toString()
    {
        return $this->libelle;
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
     * @return Collection|Nft[]
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


}
