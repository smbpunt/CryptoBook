<?php

namespace App\ProjectMonitoring\Entity;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\ProjectMonitoring\Repository\ProjectMonitoringRepository;
use App\Shared\Entity\DescriptionTrait;
use App\User\Entity\OwnedInterface;
use App\User\Entity\OwnerTrait;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectMonitoringRepository::class)]
class ProjectMonitoring implements OwnedInterface
{
    use DescriptionTrait;
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotNull]
    private $libelle;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    private $coin;

    #[ORM\Column(type: 'text')]
    private $note;

    #[ORM\Column(type: 'json')]
    private $links = [];

    #[ORM\ManyToOne(targetEntity: TypeProject::class, inversedBy: 'projectMonitorings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private $type;

    public function __construct(User $owner)
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

    public function setLibelle(?string $libelle): self
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
        if (!\in_array($link, $this->links, true)) {
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
