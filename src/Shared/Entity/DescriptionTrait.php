<?php

namespace App\Shared\Entity;

use Doctrine\ORM\Mapping as ORM;

trait DescriptionTrait
{
    #[ORM\Column(type: 'text')]
    private ?string $description;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
