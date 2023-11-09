<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait DescriptionTrait
{
    #[ORM\Column(type: 'text')]
    #[Groups(['loan:list', 'loan:item', 'position:list', 'position:item', 'farming:list', 'farming:item', 'lp:list', 'lp:item'])]
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