<?php

namespace App\FiatCurrency\Entity;

use App\FiatCurrency\Repository\FiatCurrencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiatCurrencyRepository::class)]
class FiatCurrency implements \Stringable
{
    public const string KEY_USD = 'USD';
    public const string KEY_EUR = 'EUR';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 5)]
    private ?string $fixerKey = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 5)]
    private ?string $symbol = null;

    #[ORM\Column(nullable: true)]
    private array $rates = [];

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

    public function getFixerKey(): ?string
    {
        return $this->fixerKey;
    }

    public function setFixerKey(string $fixerKey): self
    {
        $this->fixerKey = $fixerKey;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getRates(): array
    {
        return $this->rates;
    }

    public function setRates(?array $rates): self
    {
        $this->rates = $rates;

        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return (string) $this->symbol;
    }
}
