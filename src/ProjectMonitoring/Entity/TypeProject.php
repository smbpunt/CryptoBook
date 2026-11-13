<?php

namespace App\ProjectMonitoring\Entity;

use App\ProjectMonitoring\Repository\TypeProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeProjectRepository::class)]
#[UniqueEntity('libelle')]
class TypeProject implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $libelle = null;

    /** @var Collection<int, ProjectMonitoring> */
    #[ORM\OneToMany(mappedBy: 'type', targetEntity: ProjectMonitoring::class)]
    private Collection $projectMonitorings;

    public function __construct()
    {
        $this->projectMonitorings = new ArrayCollection();
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

    /**
     * @return Collection<int, ProjectMonitoring>
     */
    public function getProjectMonitorings(): Collection
    {
        return $this->projectMonitorings;
    }

    public function addProjectMonitoring(ProjectMonitoring $projectMonitoring): self
    {
        if (!$this->projectMonitorings->contains($projectMonitoring)) {
            $this->projectMonitorings[] = $projectMonitoring;
            $projectMonitoring->setType($this);
        }

        return $this;
    }

    public function removeProjectMonitoring(ProjectMonitoring $projectMonitoring): self
    {
        if ($this->projectMonitorings->removeElement($projectMonitoring)) {
            // set the owning side to null (unless already changed)
            if ($projectMonitoring->getType() === $this) {
                $projectMonitoring->setType(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->libelle;
    }
}
