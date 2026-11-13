<?php

namespace App\Strategy\Dca\Entity;

use App\Strategy\Dca\Repository\StrategyDcaRepository;
use App\User\Entity\OwnedInterface;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: StrategyDcaRepository::class)]
class StrategyDca implements OwnedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    /** @var Collection<int, CoinPercentDca> */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'strategyDca', targetEntity: CoinPercentDca::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $parts;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $fiatToDcaEur = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $farmingToDcaUsd = null;

    public function __construct(User $owner)
    {
        $this->owner = $owner;
        $this->parts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @return Collection<int, CoinPercentDca>
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    public function addPart(CoinPercentDca $part): self
    {
        if (!$this->parts->contains($part)) {
            $this->parts[] = $part;
            $part->setStrategyDca($this);
        }

        return $this;
    }

    public function removePart(CoinPercentDca $part): self
    {
        if ($this->parts->removeElement($part)) {
            // set the owning side to null (unless already changed)
            if ($part->getStrategyDca() === $this) {
                $part->setStrategyDca(null);
            }
        }

        return $this;
    }

    public function getFiatToDcaEur(): ?float
    {
        return $this->fiatToDcaEur;
    }

    public function setFiatToDcaEur(?float $fiatToDcaEur): self
    {
        $this->fiatToDcaEur = $fiatToDcaEur;

        return $this;
    }

    public function getFarmingToDcaUsd(): ?float
    {
        return $this->farmingToDcaUsd;
    }

    public function setFarmingToDcaUsd(?float $farmingToDcaUsd): self
    {
        $this->farmingToDcaUsd = $farmingToDcaUsd;

        return $this;
    }

    #[Assert\Callback]
    public function validateParts(ExecutionContextInterface $context): void
    {
        $sum = 0.0;
        foreach ($this->parts as $part) {
            $sum += (float) $part->getPercent();
        }

        if (abs($sum - 100) > 0.01) {
            $context->buildViolation('Les allocations doivent faire 100 % (actuellement {{ sum }} %).')
                ->setParameter('{{ sum }}', (string) round($sum, 2))
                ->atPath('parts')
                ->addViolation();
        }

        $coins = [];
        foreach ($this->parts as $part) {
            $coin = $part->getCoin();
            if (null === $coin) {
                continue;
            }
            if (\in_array($coin, $coins, true)) {
                $context->buildViolation('Une crypto ne peut apparaître qu\'une fois.')
                    ->atPath('parts')
                    ->addViolation();

                return;
            }
            $coins[] = $coin;
        }
    }
}
