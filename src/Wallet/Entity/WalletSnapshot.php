<?php

namespace App\Wallet\Entity;

use App\Entity\OwnedInterface;
use App\Entity\Trait\OwnerTrait;
use App\Entity\User;
use App\Wallet\Repository\WalletSnapshotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Total value of a user's wallet at a given hour, in USD.
 */
#[ORM\Entity(repositoryClass: WalletSnapshotRepository::class)]
#[ORM\UniqueConstraint(name: 'wallet_snapshot_owner_recorded_at', columns: ['owner_id', 'recorded_at'])]
class WalletSnapshot implements OwnedInterface
{
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIMETZ_IMMUTABLE)]
    private \DateTimeImmutable $recordedAt;

    #[ORM\Column(type: Types::FLOAT)]
    private float $valueUsd;

    public function __construct(User $owner, \DateTimeImmutable $recordedAt, float $valueUsd)
    {
        $this->owner = $owner;
        $this->recordedAt = $recordedAt;
        $this->valueUsd = $valueUsd;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function getValueUsd(): float
    {
        return $this->valueUsd;
    }
}
