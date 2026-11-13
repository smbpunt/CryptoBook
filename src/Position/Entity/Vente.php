<?php

namespace App\Position\Entity;

use App\Position\Repository\VenteRepository;
use App\Shared\Validator\NotInFuture;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[NotInFuture]
    private ?\DateTimeImmutable $soldAt = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\Positive]
    #[Assert\LessThanOrEqual(100)]
    private ?float $percent = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private ?float $priceSold = null;

    #[ORM\ManyToOne(targetEntity: Position::class, inversedBy: 'ventes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Position $position = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoldAt(): ?\DateTimeImmutable
    {
        return $this->soldAt;
    }

    public function setSoldAt(?\DateTimeImmutable $soldAt): self
    {
        $this->soldAt = $soldAt;

        return $this;
    }

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function setPercent(?float $percent): self
    {
        $this->percent = $percent;

        return $this;
    }

    public function getPriceSold(): ?float
    {
        return $this->priceSold;
    }

    public function setPriceSold(?float $priceSold): self
    {
        $this->priceSold = $priceSold;

        return $this;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(?Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Compared by day: a DCA position has a time, a sale does not.
     */
    #[Assert\Callback]
    public function validateSoldAfterPurchase(ExecutionContextInterface $context): void
    {
        $openedAt = $this->position?->getOpenedAt();
        if (null === $this->soldAt || null === $openedAt) {
            return;
        }

        if ($this->soldAt->format('Y-m-d') < $openedAt->format('Y-m-d')) {
            $context->buildViolation('La vente ne peut pas précéder l\'achat.')
                ->atPath('soldAt')
                ->addViolation();
        }
    }
}
