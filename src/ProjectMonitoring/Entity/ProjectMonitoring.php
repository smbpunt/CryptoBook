<?php

namespace App\ProjectMonitoring\Entity;

use App\Cryptocurrency\Entity\Cryptocurrency;
use App\ProjectMonitoring\Repository\ProjectMonitoringRepository;
use App\Shared\Entity\DescriptionTrait;
use App\User\Entity\OwnedInterface;
use App\User\Entity\OwnerTrait;
use App\User\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectMonitoringRepository::class)]
class ProjectMonitoring implements OwnedInterface
{
    use DescriptionTrait;
    use OwnerTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $libelle = null;

    #[ORM\ManyToOne(targetEntity: Cryptocurrency::class)]
    private ?Cryptocurrency $coin = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $note = null;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\All([new Assert\Url(), new Assert\Length(max: 2048)])]
    private array $links = [];

    #[ORM\ManyToOne(targetEntity: TypeProject::class, inversedBy: 'projectMonitorings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?TypeProject $type = null;

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

    /**
     * @return list<string>
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param list<string> $links
     */
    public function setLinks(array $links): self
    {
        // A CollectionType with allow_delete leaves gaps in the keys, which would be stored as a JSON object
        $this->links = array_values($links);

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
