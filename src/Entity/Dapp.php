<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DappRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DappRepository::class)]
#[ApiResource(
    collectionOperations: ['GET'],
    itemOperations: ['GET'],
    normalizationContext: ['groups' => ['loan:list', 'loan:item']]
)]
class Dapp
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $libelle;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $url;

    #[ORM\ManyToOne(targetEntity: Blockchain::class, inversedBy: 'dapps')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['loan:list', 'loan:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
    private $blockchain;

    #[ORM\OneToMany(mappedBy: 'dapp', targetEntity: Loan::class)]
    private $loans;

    #[ORM\OneToMany(mappedBy: 'dapp', targetEntity: StrategyFarming::class)]
    private $farmingStrategies;

    public function __construct()
    {
        $this->farmingStrategies = new ArrayCollection();
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

    public function __toString(): string
    {
        return $this->libelle;
    }
}
