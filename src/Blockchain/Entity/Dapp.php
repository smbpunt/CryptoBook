<?php

namespace App\Blockchain\Entity;

use App\Blockchain\Repository\DappRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DappRepository::class)]
#[UniqueEntity(fields: ['libelle', 'blockchain'], errorPath: 'libelle')]
class Dapp implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $libelle = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Assert\Length(max: 255)]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: Blockchain::class, inversedBy: 'dapps')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Blockchain $blockchain = null;

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
