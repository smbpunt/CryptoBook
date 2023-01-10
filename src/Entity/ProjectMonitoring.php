<?php

namespace App\Entity;

use App\Entity\Trait\DescriptionTrait;
use App\Entity\Trait\OwnedTrait;
use App\Repository\ProjectMonitoringRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectMonitoringRepository::class)]
class ProjectMonitoring
{
    use OwnedTrait;
    use DescriptionTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $libelle;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class, inversedBy: 'projectMonitorings')]
    private $coin;

    #[ORM\Column(type: 'text')]
    private $note;

    #[ORM\Column(type: 'array')]
    private $links = [];

    #[ORM\ManyToOne(targetEntity: TypeProject::class, inversedBy: 'projectMonitorings')]
    #[ORM\JoinColumn(nullable: false)]
    private $type;

    /**
     * @param $owner
     */
    public function __construct($owner)
    {
        $this->owner = $owner;
    }

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

    public function getCoin(): ?Cryptocurrency
    {
        return $this->coin;
    }

    public function setCoin(?Cryptocurrency $coin): self
    {
        $this->coin = $coin;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }
    public function getLinks(): ?array
    {
        return $this->links;
    }

    public function setLinks(array $links): self
    {
        $this->links = $links;

        return $this;
    }

    public function addLink(string $link): self
    {
        if (!in_array($link, $this->links, true)) {
            $this->links[] = $link;
        }

        return $this;
    }

    public function getType(): ?TypeProject
    {
        return $this->type;
    }

    public function setType(?TypeProject $type): self
    {
        $this->type = $type;

        return $this;
    }
}
