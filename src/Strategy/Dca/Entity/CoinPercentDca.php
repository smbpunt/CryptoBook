<?php

namespace App\Strategy\Dca\Entity;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\Strategy\Dca\Repository\CoinPercentDcaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CoinPercentDcaRepository::class)]
class CoinPercentDca
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Cryptocurrency $coin = null;

    #[ORM\Column(type: Types::FLOAT)]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private ?float $percent = null;

    #[ORM\ManyToOne(targetEntity: StrategyDca::class, inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?StrategyDca $strategyDca = null;

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

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function setPercent(?float $percent): self
    {
        $this->percent = $percent;

        return $this;
    }

    public function getStrategyDca(): ?StrategyDca
    {
        return $this->strategyDca;
    }

    public function setStrategyDca(?StrategyDca $strategyDca): self
    {
        $this->strategyDca = $strategyDca;

        return $this;
    }
}
