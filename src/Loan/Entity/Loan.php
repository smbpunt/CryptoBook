<?php

namespace App\Loan\Entity;

use App\Entity\Blockchain;
use App\Entity\Cryptocurrency;
use App\Entity\Dapp;
use App\Entity\OwnedInterface;
use App\Entity\Trait\DescriptionTrait;
use App\Entity\Trait\OwnerTrait;
use App\Entity\User;
use App\Loan\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
class Loan implements OwnedInterface
{
    use DescriptionTrait;
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class, inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private $coin;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    private $nbCoins;

    #[ORM\ManyToOne(targetEntity: Dapp::class, inversedBy: 'loans')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private $dapp;

    private $blockchain;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $loanedAt;

    public function __construct(User $owner)
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

    public function setNbCoins(?float $nbCoins): self
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

    public function getLoanedAt(): ?\DateTimeImmutable
    {
        return $this->loanedAt;
    }

    public function setLoanedAt(?\DateTimeImmutable $loanedAt): self
    {
        $this->loanedAt = $loanedAt;

        return $this;
    }

    public function getCurrentValue(): float
    {
        return $this->getNbCoins() * $this->coin->getPriceUsd();
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
}
