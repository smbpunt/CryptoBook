<?php

namespace App\Nft\Entity;

use App\Blockchain\Entity\Blockchain;
use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Nft\Repository\NftRepository;
use App\Shared\Entity\DescriptionTrait;
use App\Shared\Validator\NotInFuture;
use App\User\Entity\OwnedInterface;
use App\User\Entity\OwnerTrait;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: NftRepository::class)]
class Nft implements OwnedInterface
{
    use DescriptionTrait;
    use OwnerTrait;

    /** Upper bound of a PostgreSQL integer column. */
    private const int MAX_INTEGER = 2_147_483_647;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $collection = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Assert\LessThanOrEqual(self::MAX_INTEGER)]
    private ?int $num = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(self::MAX_INTEGER)]
    private ?int $rank = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(self::MAX_INTEGER)]
    private ?int $supply = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $priceCrypto = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $priceUsd = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?float $priceSoldCrypto = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\PositiveOrZero]
    private ?float $priceSoldUsd = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    #[Assert\Range(min: 0, max: 100)]
    private ?float $percentSaleFees = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[NotInFuture]
    private ?\DateTimeImmutable $purchasedOn = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[NotInFuture]
    #[Assert\GreaterThanOrEqual(propertyPath: 'purchasedOn', message: 'La vente ne peut pas précéder l\'achat.')]
    private ?\DateTimeImmutable $soldOn = null;

    #[ORM\ManyToOne(targetEntity: Blockchain::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Blockchain $blockchain = null;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Cryptocurrency $cryptocurrency = null;

    public function __construct(User $owner)
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

    public function setCollection(?string $collection): self
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

    public function setPriceCrypto(?float $priceCrypto): self
    {
        $this->priceCrypto = $priceCrypto;

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

    public function getBenefice(): float
    {
        return null === $this->soldOn ? 0 : $this->priceSoldUsd * (1 - ($this->percentSaleFees ?? 0.) / 100) - $this->priceUsd;
    }

    #[Assert\Callback]
    public function validateSale(ExecutionContextInterface $context): void
    {
        if (null !== $this->soldOn && null === $this->priceSoldUsd) {
            $context->buildViolation('Le prix de vente en dollars est obligatoire pour un NFT vendu.')
                ->atPath('priceSoldUsd')
                ->addViolation();
        }

        if (null === $this->soldOn && (null !== $this->priceSoldUsd || null !== $this->priceSoldCrypto)) {
            $context->buildViolation('La date de vente est obligatoire quand un prix de vente est saisi.')
                ->atPath('soldOn')
                ->addViolation();
        }
    }
}
