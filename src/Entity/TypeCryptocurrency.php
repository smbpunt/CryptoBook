<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TypeCryptocurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=TypeCryptocurrencyRepository::class)
 * @UniqueEntity("libelle", message="Cette cryptomonnaie existe déjà.")
 * @ApiResource()
 */
class TypeCryptocurrency
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank(message="Le libelle est obligatoire.")
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity=Cryptocurrency::class, mappedBy="type")
     */
    private $cryptocurrencies;

    public function __construct()
    {
        $this->cryptocurrencies = new ArrayCollection();
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
     * @return Collection|Cryptocurrency[]
     */
    public function getCryptocurrencies(): Collection
    {
        return $this->cryptocurrencies;
    }

    public function addCryptocurrency(Cryptocurrency $cryptocurrency): self
    {
        if (!$this->cryptocurrencies->contains($cryptocurrency)) {
            $this->cryptocurrencies[] = $cryptocurrency;
            $cryptocurrency->setType($this);
        }

        return $this;
    }

    public function removeCryptocurrency(Cryptocurrency $cryptocurrency): self
    {
        if ($this->cryptocurrencies->removeElement($cryptocurrency)) {
            // set the owning side to null (unless already changed)
            if ($cryptocurrency->getType() === $this) {
                $cryptocurrency->setType(null);
            }
        }

        return $this;
    }
}
