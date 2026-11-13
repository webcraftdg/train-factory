<?php

namespace App\Entity;

use App\Repository\ExerciseRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Exercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'exercise:detail',
        'exercises:list',
        'activity:detail',
        'activity:list',
        'block:detail',
        'session:detail',
        'session:list'
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank(message:'Le nom est obligatoire', groups:['exercise:create'])]
    #[Groups([
        'exercise:detail',
        'exercises:list',
        'activity:detail',
        'activity:list',
        'block:detail',
        'session:detail',
        'session:list'
    ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[NotBlank(message:'Le nom est obligatoire', groups:['exercise:create'])]
    #[Groups([
        'exercise:detail',
        'exercises:list',
        'activity:detail',
        'activity:list',
        'block:detail',
        'session:detail',
        'session:list'
    ])]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateUpdate = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class, mappedBy: 'exercises')]
    private Collection $activities;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateArchived = null;


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

    public function __construct()
    {
        $this->activities = new ArrayCollection();
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

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

    public function setDateUpdate(?\DateTime $dateUpdate): static
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->addExercise($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        if ($this->activities->removeElement($activity)) {
            $activity->removeExercise($this);
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

    public function getDateArchived(): ?\DateTime
    {
        return $this->dateArchived;
    }

    public function setDateArchived(?\DateTime $dateArchived): static
    {
        $this->dateArchived = $dateArchived;

        return $this;
    }

    public function canBeArchived()
    {
        return $this->getActivities()->isEmpty() && $this->dateArchived === null;
    }
}
