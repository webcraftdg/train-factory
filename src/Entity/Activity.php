<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\Regex;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['activity:create'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['activity:create'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['activity:create'])]
    private ?string $description = null;

      #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $objective = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $instruction = null;

    #[ORM\Column(length: 20)]
    #[Regex(
        pattern: '/^(?:\d+|\d\[\d\+\d\])$/',
        message: 'La durée doit être un nombre ou respecter le format 2[2+1].'
    )]
    private ?string $duration = null;

    #[ORM\Column]
    private ?int $position = null;

    /**
     * @var Collection<int, Exercise>
     */
    #[ORM\ManyToMany(targetEntity: Exercise::class, inversedBy: 'activities')]
    private Collection $exercises;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateArchived = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    /**
     * @var Collection<int, Block>
     */
    #[ORM\ManyToMany(targetEntity: Block::class, mappedBy: 'activities')]
    private Collection $blocks;


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
    public function createPosition(): void
    {
        $this->position = ($this->position) ?? 0;
    }

    public function getDurationInMinutes(): int
    {
        $duration = $this->getDuration();
        $value = 0;
        if (ctype_digit($duration)) {
            $value =  (int) $duration;
        }

        if (preg_match('/^(\d+)\[(\d+)\+(\d+)\]$/', $duration, $matches)) {
            $value =  (int) $matches[1] * ((int) $matches[2] + (int) $matches[3]);
        }
        return $value;
    }

    public function __construct()
    {
        $this->exercises = new ArrayCollection();
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

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(string $instruction): static
    {
        $this->instruction = $instruction;

        return $this;
    }

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(string $objective): static
    {
        $this->objective = $objective;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return Collection<int, Exercise>
     */
    public function getExercises(): Collection
    {
        return $this->exercises;
    }

    public function addExercise(Exercise $exercise): static
    {
        if (!$this->exercises->contains($exercise)) {
            $this->exercises->add($exercise);
        }

        return $this;
    }

    public function removeExercise(Exercise $exercise): static
    {
        $this->exercises->removeElement($exercise);

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

    /**
     * @return Collection<int, Block>
     */
    public function getBlocks(): Collection
    {
        return $this->blocks;
    }

    public function addBlock(Block $block): static
    {
        if (!$this->blocks->contains($block)) {
            $this->blocks->add($block);
            $block->addActivity($this);
        }

        return $this;
    }

    public function removeBlock(Block $block): static
    {
        if ($this->blocks->removeElement($block)) {
            $block->removeActivity($this);
        }

        return $this;
    }

    public function __toString()
    {
        $name = $this->name ?? '';
        $description = trim($this->description ?? '');

        if ($description === '') {
            return $name;
        }

        return sprintf(
            '%s — %s',
            $name,
            mb_strimwidth($description, 0, 50, '...')
        );
    }
}
