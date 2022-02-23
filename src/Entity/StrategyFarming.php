<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\StrategyFarmingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=StrategyFarmingRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"farming_read"}}
 * )
 */
class StrategyFarming
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"farming_read"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="farmingStrategies")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank (message="La cryptomonnaie est obligatoire.")
     * @Assert\NotNull (message="La cryptomonnaie est obligatoire.")
     * @Groups({"farming_read"})
     */
    private $coin;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank (message="Le nombre de coins est obligatoire.")
     */
    private $nbCoins;

    /**
     * @var Blockchain
     */
    private $blockchain;

    /**
     * @ORM\ManyToOne(targetEntity=Dapp::class, inversedBy="farmingStrategies")
     * @Assert\NotBlank (message="La dapp est obligatoire. Si votre dapp n'est pas disponible, veuillez contacter les admins.")
     * @Assert\NotNull (message="La dapp est obligatoire. Si votre dapp n'est pas disponible, veuillez contacter les admins.")
     */
    private $dapp;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank (message="L'APR est obligatoire.")
     */
    private $apr;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="farmingStrategies")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $enteredAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

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

    public function setNbCoins($nbCoins): self
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

    public function getApr(): ?float
    {
        return $this->apr;
    }

    public function setApr($apr): self
    {
        $this->apr = $apr;

        return $this;
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

    public function getEnteredAt(): ?\DateTimeImmutable
    {
        return $this->enteredAt;
    }

    public function setEnteredAt($enteredAt): self
    {
        $this->enteredAt = $enteredAt;

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
}
