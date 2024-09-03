<?php

namespace App\Entity;

use App\Entity\Trait\DescriptionTrait;
use App\Repository\NftRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NftRepository::class)]
class Nft
{
    use DescriptionTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $collection;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $num;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $rank;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $supply;

    #[ORM\Column(type: 'float')]
    private $priceCrypto;

    #[ORM\Column(type: 'float')]
    private $priceUsd;

    #[ORM\Column(type: 'float', nullable: true)]
    private $priceSoldCrypto;

    #[ORM\Column(type: 'float', nullable: true)]
    private $priceSoldUsd;

    #[ORM\Column(type: 'float', nullable: true)]
    private $percentSaleFees;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $purchasedOn;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $soldOn;

    #[ORM\ManyToOne(targetEntity: Blockchain::class, inversedBy: 'nfts')]
    #[ORM\JoinColumn(nullable: false)]
    private $blockchain;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class, inversedBy: 'nfts')]
    #[ORM\JoinColumn(nullable: false)]
    private $cryptocurrency;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'nfts')]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    /**
     * @param $owner
     */
    public function __construct($owner)
    {
        $this->owner = $owner;
        $this->description = '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCollection(): ?string
    {
        return $this->collection;
    }

    public function setCollection(string $collection): self
    {
        $this->collection = $collection;

        return $this;
    }

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function setNum(?int $num): self
    {
        $this->num = $num;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getSupply(): ?int
    {
        return $this->supply;
    }

    public function setSupply(?int $supply): self
    {
        $this->supply = $supply;

        return $this;
    }

    public function getPriceCrypto(): ?float
    {
        return $this->priceCrypto;
    }

    public function setPriceCrypto(float $priceCrypto): self
    {
        $this->priceCrypto = $priceCrypto;

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

    public function getPriceSoldCrypto(): ?float
    {
        return $this->priceSoldCrypto;
    }

    public function setPriceSoldCrypto(?float $priceSoldCrypto): self
    {
        $this->priceSoldCrypto = $priceSoldCrypto;

        return $this;
    }

    public function getPriceSoldUsd(): ?float
    {
        return $this->priceSoldUsd;
    }

    public function setPriceSoldUsd(?float $priceSoldUsd): self
    {
        $this->priceSoldUsd = $priceSoldUsd;

        return $this;
    }

    public function getPercentSaleFees(): ?float
    {
        return $this->percentSaleFees;
    }

    public function setPercentSaleFees(?float $percentSaleFees): self
    {
        $this->percentSaleFees = $percentSaleFees;

        return $this;
    }

    public function getPurchasedOn(): ?\DateTimeImmutable
    {
        return $this->purchasedOn;
    }

    public function setPurchasedOn(?\DateTimeImmutable $purchasedOn): self
    {
        $this->purchasedOn = $purchasedOn;

        return $this;
    }

    public function getSoldOn(): ?\DateTimeImmutable
    {
        return $this->soldOn;
    }

    public function setSoldOn(?\DateTimeImmutable $soldOn): self
    {
        $this->soldOn = $soldOn;

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

    public function getCryptocurrency(): ?Cryptocurrency
    {
        return $this->cryptocurrency;
    }

    public function setCryptocurrency(?Cryptocurrency $cryptocurrency): self
    {
        $this->cryptocurrency = $cryptocurrency;

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

    public function getBenefice(): float
    {
        return null === $this->soldOn ? 0 : $this->priceSoldUsd * (1 - ($this->percentSaleFees ?? 0.) / 100) - $this->priceUsd;
    }
}
