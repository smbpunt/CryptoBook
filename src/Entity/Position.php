<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PositionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @ORM\Entity(repositoryClass=PositionRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"position_read"}},
 *     collectionOperations={"GET", "POST"},
 *     itemOperations={"GET", "PUT", "DELETE"},
 *     denormalizationContext={"disable_type_enforcement" = true}
 * )
 */
class Position extends AbstractUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"position_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Groups({"position_read"})
     * @Assert\NotBlank(message="Le nombre de coin est obligatoire.")
     * @Assert\Positive(message="Le nombre de coin doit être supérieur à 0.")
     */
    private $nbCoins;

    /**
     * @ORM\ManyToOne(targetEntity=Cryptocurrency::class, inversedBy="positions")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="La cryptomonnaie est obligatoire.")
     * @Assert\NotNull(message="La cryptomonnaie est obligatoire.")
     * @Groups({"position_read"})
     */
    private $coin;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"position_read"})
     */
    private $isOpened;

    /**
     * @ORM\Column(type="float")
     * @Groups({"position_read"})
     * @Assert\NotBlank(message="Le coût est obligatoire.")
     * @Assert\PositiveOrZero(message="Le coût d'entrée doit être supérieur ou égal à 0.")
     */
    private $entryCost;

    /**
     * @ORM\OneToMany(targetEntity=Vente::class, mappedBy="position", orphanRemoval=true, cascade={"persist"})
     */
    private $ventes;

    /**
     * @ORM\OneToMany(targetEntity=StrategyPosition::class, mappedBy="position", orphanRemoval=true, cascade={"persist"})
     */
    private $strategies;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     * @Groups({"position_read"})
     */
    private $openedAt;

    /**
     * @ORM\Column(type="float")
     * @Groups({"position_read"})
     */
    private $remainingCoins;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"position_read"})
     */
    private $description;

    public function __construct()
    {
        $this->isOpened = true;
        $this->ventes = new ArrayCollection();
        $this->strategies = new ArrayCollection();
        $this->remainingCoins = 0;
        $this->description = "";
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

    public function getIsOpened(): ?bool
    {
        return $this->isOpened;
    }

    public function setIsOpened(bool $isOpened): self
    {
        $this->isOpened = $isOpened;

        return $this;
    }

    public function getEntryCoinValue(): ?float
    {
        return round(($this->getEntryCost() / $this->getNbCoins()), 2);
    }

    public function getEntryCost(): ?float
    {
        return $this->entryCost;
    }

    public function setEntryCost($entryCost): self
    {
        $this->entryCost = $entryCost;

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

    /**
     * @return Collection|Vente[]
     */
    public function getVentes(): Collection
    {
        return $this->ventes;
    }

    /**
     * @return Collection|Vente[]
     */
    public function getVentesSortedByDate(): Collection
    {
        return $this->ventes->matching(new Criteria(null, ['soldAt' => Criteria::ASC]));
    }

    public function addVente(Vente $vente): self
    {
        if (!$this->ventes->contains($vente)) {
            $this->ventes[] = $vente;
            $vente->setPosition($this);
        }

        return $this;
    }

    public function addStrategy(StrategyPosition $strategy): self
    {
        if (!$this->strategies->contains($strategy)) {
            $this->strategies[] = $strategy;
            $strategy->setPosition($this);
        }

        return $this;
    }

    public function removeVente(Vente $vente): self
    {
        if ($this->ventes->removeElement($vente)) {
            // set the owning side to null (unless already changed)
            if ($vente->getPosition() === $this) {
                $vente->setPosition(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|StrategyPosition[]
     */
    public function getStrategies(): Collection
    {
        return $this->strategies;
    }

    public function removeStrategy(StrategyPosition $strategy): self
    {
        if ($this->strategies->removeElement($strategy)) {
            // set the owning side to null (unless already changed)
            if ($strategy->getPosition() === $this) {
                $strategy->setPosition(null);
            }
        }

        return $this;
    }

    public function getOpenedAt(): ?DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function setOpenedAt($openedAt): self
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getRemainingCoins(): ?float
    {
        return $this->remainingCoins;
    }

    public function setRemainingCoins(float $remainingCoins): self
    {
        $this->remainingCoins = $remainingCoins;

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
