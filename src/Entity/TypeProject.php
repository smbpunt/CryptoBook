<?php

namespace App\Entity;

use App\Repository\TypeProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TypeProjectRepository::class)
 */
class TypeProject
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=ProjectMonitoring::class, mappedBy="type")
     */
    private $projectMonitorings;

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
     * @return Collection|ProjectMonitoring[]
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

    public function __toString()
    {
        return $this->libelle;
    }


}
