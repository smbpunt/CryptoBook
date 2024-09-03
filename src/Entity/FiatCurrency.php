<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FiatCurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiatCurrencyRepository::class)]
#[ApiResource]
class FiatCurrency
{
    public static $KEY_USD = 'USD';
    public static $KEY_EUR = 'EUR';
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
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

    #[ORM\OneToMany(mappedBy: 'fiatCurrency', targetEntity: Deposit::class, orphanRemoval: true)]
    private Collection $deposits;

    #[ORM\OneToMany(mappedBy: 'favoriteFiatCurrency', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->deposits = new ArrayCollection();
        $this->users = new ArrayCollection();
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

    public function getsymbol(): ?string
    {
        return $this->symbol;
    }

    public function setsymbol(string $symbol): self
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

    /**
     * @return Collection<int, Deposit>
     */
    public function getDeposits(): Collection
    {
        return $this->deposits;
    }

    public function addDeposit(Deposit $deposit): self
    {
        if (!$this->deposits->contains($deposit)) {
            $this->deposits->add($deposit);
            $deposit->setFiatCurrency($this);
        }

        return $this;
    }

    public function removeDeposit(Deposit $deposit): self
    {
        if ($this->deposits->removeElement($deposit)) {
            // set the owning side to null (unless already changed)
            if ($deposit->getFiatCurrency() === $this) {
                $deposit->setFiatCurrency(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->symbol;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setFavoriteFiatCurrency($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getFavoriteFiatCurrency() === $this) {
                $user->setFavoriteFiatCurrency(null);
            }
        }

        return $this;
    }
}
