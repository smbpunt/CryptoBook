<?php

namespace App\Entity;

use App\Repository\CryptocurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CryptocurrencyRepository::class)
 */
class Cryptocurrency
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelleCoingecko;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $libelle;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $priceUsd;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $priceEur;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $mcapUsd;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $mcapEur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $urlImgThumb;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $urlImgSmall;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $urlImgLarge;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $symbol;

    /**
     * @ORM\OneToMany(targetEntity=Position::class, mappedBy="coin")
     */
    private $positions;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelleCoingecko(): ?string
    {
        return $this->libelleCoingecko;
    }

    public function setLibelleCoingecko(string $libelleCoingecko): self
    {
        $this->libelleCoingecko = $libelleCoingecko;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getPriceUsd(): ?float
    {
        return $this->priceUsd;
    }

    public function setPriceUsd(?float $priceUsd): self
    {
        $this->priceUsd = $priceUsd;

        return $this;
    }

    public function getPriceEur(): ?float
    {
        return $this->priceEur;
    }

    public function setPriceEur(?float $priceEur): self
    {
        $this->priceEur = $priceEur;

        return $this;
    }

    public function getMcapUsd(): ?float
    {
        return $this->mcapUsd;
    }

    public function setMcapUsd(?float $mcapUsd): self
    {
        $this->mcapUsd = $mcapUsd;

        return $this;
    }

    public function getMcapEur(): ?float
    {
        return $this->mcapEur;
    }

    public function setMcapEur(?float $mcapEur): self
    {
        $this->mcapEur = $mcapEur;

        return $this;
    }

    public function getUrlImgThumb(): ?string
    {
        return $this->urlImgThumb;
    }

    public function setUrlImgThumb(?string $urlImgThumb): self
    {
        $this->urlImgThumb = $urlImgThumb;

        return $this;
    }

    public function getUrlImgSmall(): ?string
    {
        return $this->urlImgSmall;
    }

    public function setUrlImgSmall(?string $urlImgSmall): self
    {
        $this->urlImgSmall = $urlImgSmall;

        return $this;
    }

    public function getUrlImgLarge(): ?string
    {
        return $this->urlImgLarge;
    }

    public function setUrlImgLarge(?string $urlImgLarge): self
    {
        $this->urlImgLarge = $urlImgLarge;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(?string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return Collection|Position[]
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions[] = $position;
            $position->setCoin($this);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->removeElement($position)) {
            // set the owning side to null (unless already changed)
            if ($position->getCoin() === $this) {
                $position->setCoin(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->libelle;
    }


}
