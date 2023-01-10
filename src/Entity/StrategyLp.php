<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Trait\OwnedTrait;
use App\Repository\StrategyLpRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StrategyLpRepository::class)]
#[ApiResource(
    collectionOperations: ['GET', 'POST'],
    itemOperations: ['GET', 'PUT', 'DELETE'],
    denormalizationContext: ['disable_type_enforcement' => true, 'groups' => ['lp:write']],
    normalizationContext: ['groups' => ['lp:list', 'lp:item']]
)]
class StrategyLp
{
    use OwnedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['lp:list', 'lp:item'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['lp:list', 'lp:item'])]
    private $coin1;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['lp:list', 'lp:item'])]
    private $coin2;

    #[ORM\ManyToOne(targetEntity: Dapp::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['lp:list', 'lp:item'])]
    private $dapp;

    private $blockchain;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['lp:list', 'lp:item'])]
    private $startAt;

    #[ORM\Column(type: 'float')]
    #[Groups(['lp:list', 'lp:item'])]
    private $priceCoin1;

    #[ORM\Column(type: 'float')]
    #[Groups(['lp:list', 'lp:item'])]
    private $priceCoin2;

    #[ORM\Column(type: 'float')]
    #[Groups(['lp:list', 'lp:item'])]
    private $nbCoin1;

    #[ORM\Column(type: 'float')]
    #[Groups(['lp:list', 'lp:item'])]
    private $nbCoin2;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['lp:list', 'lp:item'])]
    private $lpDeposit;

    #[ORM\Column(type: 'float')]
    #[Groups(['lp:list', 'lp:item'])]
    private $apr;

    #[ORM\Column(type: 'text')]
    #[Groups(['lp:list', 'lp:item'])]
    private $description;

    /**
     * @param $owner
     */
    public function __construct($owner)
    {
        $this->owner = $owner;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoin1(): ?Cryptocurrency
    {
        return $this->coin1;
    }

    public function setCoin1(?Cryptocurrency $coin1): self
    {
        $this->coin1 = $coin1;

        return $this;
    }

    public function getCoin2(): ?Cryptocurrency
    {
        return $this->coin2;
    }

    public function setCoin2(?Cryptocurrency $coin2): self
    {
        $this->coin2 = $coin2;

        return $this;
    }

    public function getDapp(): ?Dapp
    {
        return $this->dapp;
    }

    public function setDapp(?Dapp $dapp): self
    {
        $this->dapp = $dapp;

        return $this;
    }

    public function getBlockchain(): ?Blockchain
    {
        if (null === $this->blockchain && null !== $this->dapp && $this->dapp->getBlockchain() !== $this->blockchain) {
            $this->blockchain = $this->dapp->getBlockchain();
        }
        return $this->blockchain;
    }

    public function setBlockchain(?Blockchain $blockchain): self
    {
        $this->blockchain = $blockchain;

        return $this;
    }

    public function getStartAt(): ?\DateTimeImmutable
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTimeImmutable $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getPriceCoin1(): ?float
    {
        return $this->priceCoin1;
    }

    public function setPriceCoin1(float $priceCoin1): self
    {
        $this->priceCoin1 = $priceCoin1;

        return $this;
    }

    public function getPriceCoin2(): ?float
    {
        return $this->priceCoin2;
    }

    public function setPriceCoin2(float $priceCoin2): self
    {
        $this->priceCoin2 = $priceCoin2;

        return $this;
    }

    public function getNbCoin1(): ?float
    {
        return $this->nbCoin1;
    }

    public function setNbCoin1(float $nbCoin1): self
    {
        $this->nbCoin1 = $nbCoin1;

        return $this;
    }

    public function getNbCoin2(): ?float
    {
        return $this->nbCoin2;
    }

    public function setNbCoin2(float $nbCoin2): self
    {
        $this->nbCoin2 = $nbCoin2;

        return $this;
    }

    public function getLpDeposit(): ?float
    {
        return $this->lpDeposit;
    }

    public function setLpDeposit(?float $lpDeposit): self
    {
        $this->lpDeposit = $lpDeposit;

        return $this;
    }

    public function getApr(): ?float
    {
        return $this->apr;
    }

    public function setApr(float $apr): self
    {
        $this->apr = $apr;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
