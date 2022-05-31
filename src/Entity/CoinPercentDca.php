<?php

namespace App\Entity;

use App\Repository\CoinPercentDcaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoinPercentDcaRepository::class)]
class CoinPercentDca
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    #[ORM\JoinColumn(nullable: false)]
    private $coin;

    #[ORM\Column(type: 'float')]
    private $percent;

    #[ORM\ManyToOne(targetEntity: StrategyDca::class, inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    private $strategyDca;

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

    public function setPercent(float $percent): self
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
