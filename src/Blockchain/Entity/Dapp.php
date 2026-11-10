<?php

namespace App\Blockchain\Entity;

use App\Blockchain\Repository\DappRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DappRepository::class)]
class Dapp implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $libelle;

    #[ORM\Column(type: 'string', length: 255)]
    private $url;

    #[ORM\ManyToOne(targetEntity: Blockchain::class, inversedBy: 'dapps')]
    #[ORM\JoinColumn(nullable: false)]
    private $blockchain;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getBlockchain(): ?Blockchain
    {
        return $this->blockchain;
    }

    public function setBlockchain(?Blockchain $blockchain): self
    {
        $this->blockchain = $blockchain;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->libelle;
    }
}
