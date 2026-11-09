<?php

namespace App\Wallet\Entity;

use App\Entity\OwnedInterface;
use App\Entity\Trait\OwnerTrait;
use App\Entity\User;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Total value of a user's wallet (crypto + stable) at a given hour, in USD.
 */
#[ORM\Entity(repositoryClass: WalletSnapshotRepository::class)]
#[ORM\UniqueConstraint(name: 'wallet_snapshot_owner_taken_at', columns: ['owner_id', 'taken_at'])]
class WalletSnapshot implements OwnedInterface
{
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(User $owner, #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $takenAt, #[ORM\Column]
        private float $valueUsd)
    {
        $this->owner = $owner;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTakenAt(): \DateTimeImmutable
    {
        return $this->takenAt;
    }

    public function getValueUsd(): float
    {
        return $this->valueUsd;
    }

    public function setValueUsd(float $valueUsd): self
    {
        $this->valueUsd = $valueUsd;

        return $this;
    }
}
