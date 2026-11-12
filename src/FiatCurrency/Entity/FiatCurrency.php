<?php

namespace App\FiatCurrency\Entity;

use App\FiatCurrency\Exception\MissingRateException;
use App\FiatCurrency\Repository\FiatCurrencyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $libelle = null;

    /** ISO 4217 code, as Fixer knows it. */
    #[ORM\Column(length: 5)]
    #[Assert\NotBlank]
    #[Assert\Currency]
    private ?string $fixerKey = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 5)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 5)]
    private ?string $symbol = null;

    /** @var array<string, float> */
    #[ORM\Column]
    private array $rates = [];

    public function getId(): ?int
    {
        return $this->id;
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

    public function getFixerKey(): ?string
    {
        return $this->fixerKey;
    }

    public function setFixerKey(?string $fixerKey): self
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

    public function setSymbol(?string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return array<string, float>
     */
    public function getRates(): array
    {
        return $this->rates;
    }

    /**
     * @param array<string, float> $rates
     */
    public function setRates(array $rates): self
    {
        $this->rates = $rates;

        return $this;
    }

    /**
     * Value of one unit of this currency in the other one.
     *
     * @throws MissingRateException
     */
    public function rateTo(string $fixerKey): float
    {
        if ($fixerKey === $this->fixerKey) {
            return 1.0;
        }

        return $this->rates[$fixerKey] ?? throw MissingRateException::between((string) $this->fixerKey, $fixerKey);
    }

    public function __toString(): string
    {
        return (string) $this->symbol;
    }
}
