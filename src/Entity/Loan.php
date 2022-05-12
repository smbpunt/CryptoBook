<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LoanRepository::class)
 */
class Loan
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="loans")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="loans")
     * @ORM\JoinColumn(nullable=false)
     */
    private $coin;

    /**
     * @ORM\Column(type="float")
     */
    private $nbCoins;

    /**
     * @var Blockchain
     */
    private $blockchain;

    /**
     * @ORM\ManyToOne(targetEntity=Dapp::class, inversedBy="loans")
     */
    private $dapp;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $loanedAt;

    /**
     * @param $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

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

    public function setDescription(?string $description): self
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
}
