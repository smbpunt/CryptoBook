<?php

namespace App\Position\Entity;

use App\Position\Repository\StrategyPositionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StrategyPositionRepository::class)]
class StrategyPosition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private $percent;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private $priceSold;

    #[ORM\ManyToOne(targetEntity: Position::class, inversedBy: 'strategies')]
    #[ORM\JoinColumn(nullable: false)]
    private $position;

    public function getId(): ?int
    {
        return $this->id;
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
}
