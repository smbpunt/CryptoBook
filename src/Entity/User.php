<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Deposit::class, orphanRemoval: true)]
    private $deposits;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Nft::class, orphanRemoval: true)]
    private $nfts;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Position::class, orphanRemoval: true)]
    private $positions;

    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: StrategyDca::class, cascade: ['persist', 'remove'])]
    private $strategyDca;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Loan::class, orphanRemoval: true)]
    private $loans;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ProjectMonitoring::class, orphanRemoval: true)]
    private $projectMonitorings;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: StrategyFarming::class, orphanRemoval: true)]
    private $farmingStrategies;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: StrategyLp::class, orphanRemoval: true)]
    private $strategyLps;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->deposits = new ArrayCollection();
        $this->farmingStrategies = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->strategyLps = new ArrayCollection();
        $this->nfts = new ArrayCollection();
        $this->projectMonitorings = new ArrayCollection();
        $this->roles[] = 'ROLE_USER';
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
        return (string) $this->email;
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
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $this->deposits[] = $deposit;
            $deposit->setOwner($this);
        }

        return $this;
    }

    public function removeDeposit(Deposit $deposit): self
    {
        if ($this->deposits->removeElement($deposit)) {
            // set the owning side to null (unless already changed)
            if ($deposit->getOwner() === $this) {
                $deposit->setOwner(null);
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
            $nft->setOwner($this);
        }

        return $this;
    }

    public function removeNft(Nft $nft): self
    {
        if ($this->nfts->removeElement($nft)) {
            // set the owning side to null (unless already changed)
            if ($nft->getOwner() === $this) {
                $nft->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Position>
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions[] = $position;
            $position->setOwner($this);
        }

        return $this;
    }

    public function removePosition(Position $position): self
    {
        if ($this->positions->removeElement($position)) {
            // set the owning side to null (unless already changed)
            if ($position->getOwner() === $this) {
                $position->setOwner(null);
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
        if ($strategyDca->getOwner() !== $this) {
            $strategyDca->setOwner($this);
        }

        $this->strategyDca = $strategyDca;

        return $this;
    }

    /**
     * @return Collection<int, Loan>
     */
    public function getLoans(): Collection
    {
        return $this->loans;
    }

    public function addLoan(Loan $loan): self
    {
        if (!$this->loans->contains($loan)) {
            $this->loans[] = $loan;
            $loan->setOwner($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): self
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getOwner() === $this) {
                $loan->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectMonitoring>
     */
    public function getProjectMonitorings(): Collection
    {
        return $this->projectMonitorings;
    }

    public function addProjectMonitoring(ProjectMonitoring $projectMonitoring): self
    {
        if (!$this->projectMonitorings->contains($projectMonitoring)) {
            $this->projectMonitorings[] = $projectMonitoring;
            $projectMonitoring->setOwner($this);
        }

        return $this;
    }

    public function removeProjectMonitoring(ProjectMonitoring $projectMonitoring): self
    {
        if ($this->projectMonitorings->removeElement($projectMonitoring)) {
            // set the owning side to null (unless already changed)
            if ($projectMonitoring->getOwner() === $this) {
                $projectMonitoring->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StrategyFarming>
     */
    public function getFarmingStrategies(): Collection
    {
        return $this->farmingStrategies;
    }

    public function addFarmingStrategy(StrategyFarming $farmingStrategy): self
    {
        if (!$this->farmingStrategies->contains($farmingStrategy)) {
            $this->farmingStrategies[] = $farmingStrategy;
            $farmingStrategy->setOwner($this);
        }

        return $this;
    }

    public function removeFarmingStrategy(StrategyFarming $farmingStrategy): self
    {
        if ($this->farmingStrategies->removeElement($farmingStrategy)) {
            // set the owning side to null (unless already changed)
            if ($farmingStrategy->getOwner() === $this) {
                $farmingStrategy->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StrategyLp>
     */
    public function getStrategyLps(): Collection
    {
        return $this->strategyLps;
    }

    public function addStrategyLp(StrategyLp $strategyLp): self
    {
        if (!$this->strategyLps->contains($strategyLp)) {
            $this->strategyLps[] = $strategyLp;
            $strategyLp->setOwner($this);
        }

        return $this;
    }

    public function removeStrategyLp(StrategyLp $strategyLp): self
    {
        if ($this->strategyLps->removeElement($strategyLp)) {
            // set the owning side to null (unless already changed)
            if ($strategyLp->getOwner() === $this) {
                $strategyLp->setOwner(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
