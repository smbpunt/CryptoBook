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
     * @ORM\Column(type="string", length=255, unique=true)
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
    private $mcapUsd;

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
     * @ORM\Column(type="string", length=8, nullable=true)
     */
    private $symbol;

    /**
     * @ORM\OneToMany(targetEntity=Position::class, mappedBy="coin")
     */
    private $positions;

    /**
     * @ORM\OneToMany(targetEntity=StrategyFarming::class, mappedBy="coin", orphanRemoval=true)
     */
    private $farmingStrategies;

    /**
     * @ORM\Column(type="boolean", options={"default" : false}, nullable=false)
     */
    private $isStable;

    /**
     * @ORM\OneToMany(targetEntity=Blockchain::class, mappedBy="coin")
     */
    private $blockchains;

    /**
     * @ORM\OneToMany(targetEntity=Loan::class, mappedBy="coin", orphanRemoval=true)
     */
    private $loans;

    /**
     * @ORM\OneToMany(targetEntity=StrategyLP::class, mappedBy="coin1", orphanRemoval=true)
     */
    private $strategyLp1s;

    /**
     * @ORM\OneToMany(targetEntity=StrategyLP::class, mappedBy="coin2", orphanRemoval=true)
     */
    private $strategyLp2s;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $color;

    /**
     * @ORM\OneToMany(targetEntity=Nft::class, mappedBy="cryptocurrency")
     */
    private $nfts;

    /**
     * @ORM\OneToMany(targetEntity=ProjectMonitoring::class, mappedBy="coin")
     */
    private $userProjectMonitorings;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->farmingStrategies = new ArrayCollection();
        $this->blockchains = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->strategyLp1s = new ArrayCollection();
        $this->strategyLp2s = new ArrayCollection();
        $this->nfts = new ArrayCollection();
        $this->userProjectMonitorings = new ArrayCollection();
        $this->isStable = false;
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
        return $this->symbol ? strtoupper($this->symbol) : "Non défini";
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
            $farmingStrategy->setCoin($this);
        }

        return $this;
    }

    public function removeFarmingStrategy(StrategyFarming $farmingStrategy): self
    {
        if ($this->farmingStrategies->removeElement($farmingStrategy)) {
            // set the owning side to null (unless already changed)
            if ($farmingStrategy->getCoin() === $this) {
                $farmingStrategy->setCoin(null);
            }
        }

        return $this;
    }

    public function getIsStable(): ?bool
    {
        return $this->isStable;
    }

    public function setIsStable(bool $isStable): self
    {
        $this->isStable = $isStable;

        return $this;
    }

    /**
     * @return Collection|Blockchain[]
     */
    public function getBlockchains(): Collection
    {
        return $this->blockchains;
    }

    public function addBlockchain(Blockchain $blockchain): self
    {
        if (!$this->blockchains->contains($blockchain)) {
            $this->blockchains[] = $blockchain;
            $blockchain->setCoin($this);
        }

        return $this;
    }

    public function removeBlockchain(Blockchain $blockchain): self
    {
        if ($this->blockchains->removeElement($blockchain)) {
            // set the owning side to null (unless already changed)
            if ($blockchain->getCoin() === $this) {
                $blockchain->setCoin(null);
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
            $loan->setCoin($this);
        }

        return $this;
    }

    public function removeLoan(Loan $loan): self
    {
        if ($this->loans->removeElement($loan)) {
            // set the owning side to null (unless already changed)
            if ($loan->getCoin() === $this) {
                $loan->setCoin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StrategyLP[]
     */
    public function getStrategyLp1s(): Collection
    {
        return $this->strategyLp1s;
    }

    public function addStrategyLp1(StrategyLP $strategyLP): self
    {
        if (!$this->strategyLp1s->contains($strategyLP)) {
            $this->strategyLp1s[] = $strategyLP;
            $strategyLP->setCoin1($this);
        }

        return $this;
    }

    public function removeStrategyLp1(StrategyLP $strategyLP): self
    {
        if ($this->strategyLp1s->removeElement($strategyLP)) {
            // set the owning side to null (unless already changed)
            if ($strategyLP->getCoin1() === $this) {
                $strategyLP->setCoin1(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StrategyLP[]
     */
    public function getStrategyLp2s(): Collection
    {
        return $this->strategyLp2s;
    }

    public function addStrategyLp2(StrategyLP $strategyLP): self
    {
        if (!$this->strategyLp2s->contains($strategyLP)) {
            $this->strategyLp2s[] = $strategyLP;
            $strategyLP->setCoin2($this);
        }

        return $this;
    }

    public function removeStrategyLp2(StrategyLP $strategyLP): self
    {
        if ($this->strategyLp2s->removeElement($strategyLP)) {
            // set the owning side to null (unless already changed)
            if ($strategyLP->getCoin2() === $this) {
                $strategyLP->setCoin2(null);
            }
        }

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
            $nft->setCryptocurrency($this);
        }

        return $this;
    }

    public function removeNft(Nft $nft): self
    {
        if ($this->nfts->removeElement($nft)) {
            // set the owning side to null (unless already changed)
            if ($nft->getCryptocurrency() === $this) {
                $nft->setCryptocurrency(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProjectMonitoring[]
     */
    public function getUserProjectMonitorings(): Collection
    {
        return $this->userProjectMonitorings;
    }

    public function addUserProjectMonitoring(ProjectMonitoring $userProjectMonitoring): self
    {
        if (!$this->userProjectMonitorings->contains($userProjectMonitoring)) {
            $this->userProjectMonitorings[] = $userProjectMonitoring;
            $userProjectMonitoring->setCoin($this);
        }

        return $this;
    }

    public function removeUserProjectMonitoring(ProjectMonitoring $userProjectMonitoring): self
    {
        if ($this->userProjectMonitorings->removeElement($userProjectMonitoring)) {
            // set the owning side to null (unless already changed)
            if ($userProjectMonitoring->getCoin() === $this) {
                $userProjectMonitoring->setCoin(null);
            }
        }

        return $this;
    }


}
