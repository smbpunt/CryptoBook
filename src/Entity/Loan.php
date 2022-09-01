<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
#[ApiResource(
    collectionOperations: ['GET', 'POST'],
    itemOperations: ['GET', 'PUT', 'DELETE'],
    denormalizationContext: ['disable_type_enforcement' => true, 'groups' => ['loan:write']],
    normalizationContext: ['groups' => ['loan:list', 'loan:item']]
)]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['loan:list', 'loan:item'])]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    private $owner;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class, inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['loan:list', 'loan:item'])]
    private $coin;

    #[ORM\Column(type: 'float')]
    #[Groups(['loan:list', 'loan:item'])]
    private $nbCoins;

    #[ORM\ManyToOne(targetEntity: Dapp::class, inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['loan:list', 'loan:item'])]
    private $dapp;

    private $blockchain;

    #[ORM\Column(type: 'text')]
    #[Groups(['loan:list', 'loan:item'])]
    private $description;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['loan:list', 'loan:item'])]
    private $loanedAt;

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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLoanedAt(): ?\DateTimeImmutable
    {
        return $this->loanedAt;
    }

    public function setLoanedAt(?\DateTimeImmutable $loanedAt): self
    {
        $this->loanedAt = $loanedAt;

        return $this;
    }

    #[Groups(['loan:item', 'loan:list'])]
    public function getCurrentValue(): float
    {
        return $this->getNbCoins() * $this->coin->getPriceUsd();
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
}
