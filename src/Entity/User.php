<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\OneToMany(targetEntity=Position::class, mappedBy="user", orphanRemoval=true)
     */
    private $positions;

    /**
     * @ORM\OneToMany(targetEntity=Deposit::class, mappedBy="user", orphanRemoval=true)
     */
    private $deposits;

    /**
     * @ORM\OneToOne(targetEntity=StrategyDca::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $strategyDca;

    /**
     * @ORM\OneToMany(targetEntity=StrategyFarming::class, mappedBy="user", orphanRemoval=true)
     */
    private $farmingStrategies;

    /**
     * @ORM\OneToMany(targetEntity=Loan::class, mappedBy="user", orphanRemoval=true)
     */
    private $loans;

    /**
     * @ORM\OneToMany(targetEntity=StrategyLP::class, mappedBy="user", orphanRemoval=true)
     */
    private $strategyLPs;

    /**
     * @ORM\OneToMany(targetEntity=Nft::class, mappedBy="user", orphanRemoval=true)
     */
    private $nfts;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->deposits = new ArrayCollection();
        $this->farmingStrategies = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->strategyLPs = new ArrayCollection();
        $this->nfts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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
            $position->setUser($this);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->removeElement($position)) {
            // set the owning side to null (unless already changed)
            if ($position->getUser() === $this) {
                $position->setUser(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->email;
    }

    /**
     * @return Collection|Deposit[]
     */
    public function getDeposits(): Collection
    {
        return $this->deposits;
    }

    public function addDeposit(Deposit $deposit): self
    {
        if (!$this->deposits->contains($deposit)) {
            $this->deposits[] = $deposit;
            $deposit->setUser($this);
        }

        return $this;
    }

    public function removeDeposit(Deposit $deposit): self
    {
        if ($this->deposits->removeElement($deposit)) {
            // set the owning side to null (unless already changed)
            if ($deposit->getUser() === $this) {
                $deposit->setUser(null);
            }
        }

        return $this;
    }

    public function getStrategyDca(): ?StrategyDca
    {
        return $this->strategyDca;
    }

    public function setStrategyDca(StrategyDca $strategyDca): self
    {
        // set the owning side of the relation if necessary
        if ($strategyDca->getUser() !== $this) {
            $strategyDca->setUser($this);
        }

        $this->strategyDca = $strategyDca;

        return $this;
    }

    /**
     * @return Collection|StrategyFarming[]
     */
    public function getFarmingStrategies(): Collection
    {
        return $this->farmingStrategies;
    }

    public function addFarmingStrategy(StrategyFarming $farmingStrategy): self
    {
        if (!$this->farmingStrategies->contains($farmingStrategy)) {
            $this->farmingStrategies[] = $farmingStrategy;
            $farmingStrategy->setUser($this);
        }

        return $this;
    }

    public function removeFarmingStrategy(StrategyFarming $farmingStrategy): self
    {
        if ($this->farmingStrategies->removeElement($farmingStrategy)) {
            // set the owning side to null (unless already changed)
            if ($farmingStrategy->getUser() === $this) {
                $farmingStrategy->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Loan[]
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): self
    {
        if (!$this->loans->contains($loan)) {
            $this->loans[] = $loan;
            $loan->setUser($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): self
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getUser() === $this) {
                $loan->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StrategyLP[]
     */
    public function getStrategyLPs(): Collection
    {
        return $this->strategyLPs;
    }

    public function addStrategyLP(StrategyLP $strategyLP): self
    {
        if (!$this->strategyLPs->contains($strategyLP)) {
            $this->strategyLPs[] = $strategyLP;
            $strategyLP->setUser($this);
        }

        return $this;
    }

    public function removeStrategyLP(StrategyLP $strategyLP): self
    {
        if ($this->strategyLPs->removeElement($strategyLP)) {
            // set the owning side to null (unless already changed)
            if ($strategyLP->getUser() === $this) {
                $strategyLP->setUser(null);
            }
        }

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
            $nft->setUser($this);
        }

        return $this;
    }

    public function removeNft(Nft $nft): self
    {
        if ($this->nfts->removeElement($nft)) {
            // set the owning side to null (unless already changed)
            if ($nft->getUser() === $this) {
                $nft->setUser(null);
            }
        }

        return $this;
    }


}
