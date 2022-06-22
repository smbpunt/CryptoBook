<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CryptocurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CryptocurrencyRepository::class)]
#[UniqueEntity('libelleCoingecko', message: 'Cette cryptomonnaie existe déjà.')]
#[ApiResource(
    collectionOperations: ['GET', 'POST'],
    itemOperations: ['GET', 'PUT', 'DELETE', 'PATCH'],
    denormalizationContext: ['disable_type_enforcement' => true, 'groups' => ['crypto:write']],
    normalizationContext: ['groups' => ['crypto:item', 'crypto:list']]
)]
class Cryptocurrency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['crypto:item', 'crypto:list', 'position:item', 'position:list', 'loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $libelleCoingecko;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['crypto:item', 'crypto:list', 'position:item', 'position:list', 'loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $libelle;

    #[ORM\Column(type: 'float')]
    #[Groups(['crypto:item', 'crypto:list', 'position:item', 'position:list', 'loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $priceUsd;

    #[ORM\Column(type: 'float')]
    private $mcapUsd;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['crypto:item', 'crypto:list', 'position:item', 'position:list', 'loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $urlImgThumb;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $urlImgSmall;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $urlImgLarge;

    #[ORM\Column(type: 'string', length: 8)]
    #[Groups(['crypto:item', 'crypto:list', 'position:item', 'position:list', 'loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $symbol;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Groups(['crypto:item', 'crypto:list', 'position:item', 'position:list', 'loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $color;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['crypto:item', 'crypto:list','position:item', 'position:list', 'loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $isStable;

    #[ORM\OneToMany(mappedBy: 'coin', targetEntity: Blockchain::class)]
    private $blockchains;

    #[ORM\OneToMany(mappedBy: 'cryptocurrency', targetEntity: Nft::class)]
    private $nfts;

    #[ORM\OneToMany(mappedBy: 'coin', targetEntity: Position::class)]
    private $positions;

    #[ORM\OneToMany(mappedBy: 'coin', targetEntity: Loan::class)]
    private $loans;

    #[ORM\OneToMany(mappedBy: 'coin', targetEntity: ProjectMonitoring::class)]
    private $projectMonitorings;

    #[ORM\OneToMany(mappedBy: 'coin', targetEntity: StrategyFarming::class)]
    private $farmingStrategies;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
        $this->farmingStrategies = new ArrayCollection();
        $this->blockchains = new ArrayCollection();
        $this->loans = new ArrayCollection();
        $this->nfts = new ArrayCollection();
        $this->projectMonitorings = new ArrayCollection();
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

    /**
     * @return Collection<int, Blockchain>
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
            $projectMonitoring->setCoin($this);
        }

        return $this;
    }

    public function removeProjectMonitoring(ProjectMonitoring $projectMonitoring): self
    {
        if ($this->projectMonitorings->removeElement($projectMonitoring)) {
            // set the owning side to null (unless already changed)
            if ($projectMonitoring->getCoin() === $this) {
                $projectMonitoring->setCoin(null);
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
        return $this->symbol ? strtoupper($this->symbol) : "Non défini";
    }
}
