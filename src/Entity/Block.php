<?php

namespace App\Entity;

use App\Repository\BlockRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: BlockRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Block
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['block:detail', 'block:list', 'session:detail'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank(groups:['block:create', 'block:update'])]
    #[Groups(['block:detail', 'block:list', 'session:detail'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[NotBlank(groups:['block:create', 'block:update'])]
    #[Groups(['block:detail', 'block:list', 'session:detail'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['block:detail', 'block:list', 'session:detail'])]
    private ?string $objective = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['block:detail', 'block:list', 'session:detail'])]
    private ?string $instruction = null;

    #[ORM\Column]
    #[Groups(['block:detail', 'block:list', 'session:detail'])]
    private ?int $duration = null;

    /**
     * @var Collection<int, Session>
     */
    #[ORM\ManyToMany(targetEntity: Session::class, mappedBy: 'blocks')]
    private Collection $sessions;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['block:detail'])]
    private ?\DateTime $dateArchived = null;

    #[ORM\Column]
    private ?\DateTime $dateCreate = null;

    #[ORM\Column]
    private ?\DateTime $dateUpdate = null;

    /**
     * @var Collection<int, Activity>
     */
    #[ORM\ManyToMany(targetEntity: Activity::class, inversedBy: 'blocks')]
    #[Groups(['block:detail',  'session:detail'])]
    private Collection $activities;

    #[ORM\Column(nullable: true)]
    private ?array $activityOrder = null;


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
        $durations = $this->getActivities()->reduce(
            fn(int $total, Activity $activity) =>
                $total + (int) $activity->getDurationInMinutes(),
            0
        );
        $this->setDuration($durations);
    }

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
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

    public function getObjective(): ?string
    {
        return $this->objective;
    }

    public function setObjective(?string $objective): static
    {
        $this->objective = $objective;
        return $this;
    }

    public function getInstruction(): ?string
    {
        return $this->instruction;
    }

    public function setInstruction(?string $instruction): static
    {
        $this->instruction = $instruction;

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
     * @return Collection<int, Session>
     */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->addBlock($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session)) {
            $session->removeBlock($this);
        }

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
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function getOrderedActivities(): array
    {
        $activities = $this->activities->toArray();
        $order = array_flip($this->activityOrder ?? []);

        usort($activities, static function (Activity $a, Activity $b) use ($order): int {
            return ($order[$a->getId()] ?? PHP_INT_MAX) <=> ($order[$b->getId()] ?? PHP_INT_MAX);
        });
        return $activities;
    }

    public function addActivity(Activity $activity): static
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): static
    {
        $this->activities->removeElement($activity);

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

    public function canBeArchived()
    {
        return $this->sessions->isEmpty() && $this->dateArchived === null;
    }

    public function getActivityOrder(): ?array
    {
        return $this->activityOrder;
    }

    public function setActivityOrder(?array $activityOrder): static
    {
        $this->activityOrder = $activityOrder;

        return $this;
    }
}
