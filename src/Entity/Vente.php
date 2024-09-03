<?php

namespace App\Entity;

use App\Repository\VenteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $soldAt;

    #[ORM\Column(type: 'float')]
    private $percent;

    #[ORM\Column(type: 'float')]
    private $priceSold;

    #[ORM\ManyToOne(targetEntity: Position::class, inversedBy: 'ventes')]
    #[ORM\JoinColumn(nullable: false)]
    private $position;

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

    public function setPercent(float $percent): self
    {
        $this->percent = $percent;

        return $this;
    }

    public function getPriceSold(): ?float
    {
        return $this->priceSold;
    }

    public function setPriceSold(float $priceSold): self
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
