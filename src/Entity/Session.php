<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(
    fields: ['name'],
    message: 'Une séance avec ce nom existe déjà.'
)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['session:detail', 'session:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank(groups:['session:create', 'session:update'])]
    #[Groups(['session:detail', 'session:list'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['session:detail', 'session:list'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[NotBlank(groups:['session:create', 'session:update'])]
    #[Groups(['session:detail', 'session:list'])]
    private ?string $target = null;

    #[ORM\Column]
    private ?int $duration = null;

    /**
     * @var Collection<int, Block>
     */
    #[ORM\ManyToMany(targetEntity: Block::class, inversedBy: 'sessions')]
    #[Groups(['session:detail', 'session:list'])]
    private Collection $blocks;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateArchived = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    #[ORM\Column]
    private ?array $blockOrder = [];

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamp(): void
    {
        $this->dateUpdate = new DateTime();
    }

    #[ORM\PrePersist]
    public function createTimestamp(): void
    {
        $this->dateCreate = new DateTime();
    }
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateDuration(): void
    {
        $durations = $this->getBlocks()->reduce(
            fn(int $total, Block $block) =>
                $total + (int) $block->getDuration(),
            0
        );
        $this->setDuration($durations);
    }

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(string $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function getOrderedBlocks(): array
    {
        $blocks = $this->blocks->toArray();
        $order = array_flip($this->blockOrder ?? []);

        usort($blocks, static function (Block $a, Block $b) use ($order): int {
            return ($order[$a->getId()] ?? PHP_INT_MAX) <=> ($order[$b->getId()] ?? PHP_INT_MAX);
        });

        return $blocks;
    }

    public function addBlock(Block $block): static
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
        }

        return $this;
    }

    public function removeBlock(Block $block): static
    {
        $this->blocks->removeElement($block);

        return $this;
    }

    public function getDateArchived(): ?\DateTime
    {
        return $this->dateArchived;
    }

    public function setDateArchived(?\DateTime $dateArchived): static
    {
        $this->dateArchived = $dateArchived;

        return $this;
    }

    public function getDateCreate(): ?\DateTime
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTime $dateCreate): static
    {
        $this->dateCreate = $dateCreate;

        return $this;
    }

    public function getDateUpdate(): ?\DateTime
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(\DateTime $dateUpdate): static
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    public function getBlockOrder(): ?array
    {
        return $this->blockOrder;
    }

    public function setBlockOrder(array $blockOrder): static
    {
        $this->blockOrder = $blockOrder;

        return $this;
    }

    public function toArray() : array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'target' => $this->getTarget(),
            'blocks' => []
        ];
    }
}
