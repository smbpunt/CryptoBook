<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Trait\DescriptionTrait;
use App\Repository\StrategyFarmingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StrategyFarmingRepository::class)]
#[ApiResource(
    collectionOperations: ['GET', 'POST'],
    itemOperations: ['GET', 'PUT', 'DELETE'],
    denormalizationContext: ['disable_type_enforcement' => true, 'groups' => ['farming:write']],
    normalizationContext: ['groups' => ['farming:list', 'farming:item']]
)]
class StrategyFarming
{
    use DescriptionTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['farming:list', 'farming:item'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class, inversedBy: 'farmingStrategies')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['farming:list', 'farming:item'])]
    private $coin;

    #[ORM\Column(type: 'float')]
    #[Groups(['farming:list', 'farming:item'])]
    private $nbCoins;

    #[ORM\ManyToOne(targetEntity: Dapp::class, inversedBy: 'farmingStrategies')]
    #[Groups(['farming:list', 'farming:item'])]
    private $dapp;

    private $blockchain;

    #[ORM\Column(type: 'float')]
    #[Groups(['farming:list', 'farming:item'])]
    private $apr;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['farming:list', 'farming:item'])]
    private $enteredAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'farmingStrategies')]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

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

    public function getCoin(): ?Cryptocurrency
    {
        return $this->coin;
    }

    public function setCoin(?Cryptocurrency $coin): self
    {
        $this->coin = $coin;

        return $this;
    }

    public function getNbCoins(): ?float
    {
        return $this->nbCoins;
    }

    public function setNbCoins(float $nbCoins): self
    {
        $this->nbCoins = $nbCoins;

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

    public function getApr(): ?float
    {
        return $this->apr;
    }

    public function setApr(float $apr): self
    {
        $this->apr = $apr;

        return $this;
    }

    public function getEnteredAt(): ?\DateTimeImmutable
    {
        return $this->enteredAt;
    }

    public function setEnteredAt(?\DateTimeImmutable $enteredAt): self
    {
        $this->enteredAt = $enteredAt;

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

    public function getBlockchain(): ?Blockchain
    {
        if ($this->blockchain === null && $this->dapp !== null && $this->dapp->getBlockchain() !== $this->blockchain) {
            $this->blockchain = $this->dapp->getBlockchain();
        }
        return $this->blockchain;
    }

    public function setBlockchain(?Blockchain $blockchain): self
    {
        $this->blockchain = $blockchain;

        return $this;
    }

    public function getValueUsd(): float
    {
        return $this->nbCoins * $this->coin->getPriceUsd();
    }

    public function getAnnualRewardsUsd(): float
    {
        return $this->apr * $this->getValueUsd() / 100;
    }
}
