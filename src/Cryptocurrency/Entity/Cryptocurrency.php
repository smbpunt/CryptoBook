<?php

namespace App\Cryptocurrency\Entity;

use App\Cryptocurrency\Repository\CryptocurrencyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CryptocurrencyRepository::class)]
#[UniqueEntity('libelleCoingecko', message: 'Cette cryptomonnaie existe déjà.')]
class Cryptocurrency implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $libelleCoingecko = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $priceUsd = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $mcapUsd = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $urlImgThumb = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $urlImgSmall = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $urlImgLarge = null;

    #[ORM\Column(type: Types::STRING, length: 8)]
    private ?string $symbol = null;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    #[Assert\CssColor(Assert\CssColor::HEX_LONG)]
    private ?string $color = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isStable = null;

    public function __construct()
    {
        $this->isStable = false;
        $this->priceUsd = 0;
        $this->mcapUsd = 0;
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

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getPriceUsd(): ?float
    {
        return $this->priceUsd;
    }

    public function setPriceUsd(float $priceUsd): self
    {
        $this->priceUsd = $priceUsd;

        return $this;
    }

    public function getMcapUsd(): ?float
    {
        return $this->mcapUsd;
    }

    public function setMcapUsd(float $mcapUsd): self
    {
        $this->mcapUsd = $mcapUsd;

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

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function isIsStable(): ?bool
    {
        return $this->isStable;
    }

    public function setIsStable(bool $isStable): self
    {
        $this->isStable = $isStable;

        return $this;
    }

    public function __toString(): string
    {
        return $this->symbol ? strtoupper($this->symbol) : 'Non défini';
    }
}
