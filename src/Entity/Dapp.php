<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DappRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=DappRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"dapps_read"}},
 * )
 */
class Dapp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"dapps_read", "farming_read", "blockchains_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"dapps_read", "farming_read", "blockchains_read"})
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"dapps_read", "farming_read"})
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity=Blockchain::class, inversedBy="dapps")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"dapps_read", "farming_read"})
     */
    private $blockchain;

    /**
     * @ORM\OneToMany(targetEntity=StrategyFarming::class, mappedBy="dapp")
     */
    private $farmingStrategies;

    /**
     * @ORM\OneToMany(targetEntity=StrategyLP::class, mappedBy="dapp", orphanRemoval=true)
     */
    private $strategyLPs;

    /**
     * @ORM\OneToMany(targetEntity=Loan::class, mappedBy="dapp")
     */
    private $loans;

    public function __construct()
    {
        $this->farmingStrategies = new ArrayCollection();
        $this->strategyLPs = new ArrayCollection();
        $this->loans = new ArrayCollection();
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getBlockchain(): ?Blockchain
    {
        return $this->blockchain;
    }

    public function setBlockchain(?Blockchain $blockchain): self
    {
        $this->blockchain = $blockchain;

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
            $farmingStrategy->setDapp($this);
        }

        return $this;
    }

    public function removeFarmingStrategy(StrategyFarming $farmingStrategy): self
    {
        if ($this->farmingStrategies->removeElement($farmingStrategy)) {
            // set the owning side to null (unless already changed)
            if ($farmingStrategy->getDapp() === $this) {
                $farmingStrategy->setDapp(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->libelle;
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
            $strategyLP->setDapp($this);
        }

        return $this;
    }

    public function removeStrategyLP(StrategyLP $strategyLP): self
    {
        if ($this->strategyLPs->removeElement($strategyLP)) {
            // set the owning side to null (unless already changed)
            if ($strategyLP->getDapp() === $this) {
                $strategyLP->setDapp(null);
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
            $loan->setDapp($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): self
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getDapp() === $this) {
                $loan->setDapp(null);
            }
        }

        return $this;
    }


}
