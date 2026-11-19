<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Block;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class ActivityResolver
{
    public function __construct(
        private readonly ActivityRepository $activityRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly ExerciseResolver $exerciseResolver,
    ) {
    }

    /**
     * resolve
     *
     * @param  array             $data
     * @param  \App\Entity\Block $entity
     * @param  int               $entityIndex
     *
     * @return ErrorResolver
     */
    public function resolve(array $data, Block $entity, int $entityIndex = 0): ErrorResolver
    {
        $errors = [];
        foreach($data as $index => $item) {
            $id = ($item['id']) ?? null;
            $group = 'activity:create';
            if ($id === null) {
                $activity = new Activity();
                $this->entityManager->persist($activity);
            } else {
                $group = 'activity:update';
                $activity = $this->activityRepository->find($id);
                if ($activity === null) {
                    $errors[] = [
                        'path' => 'block['.$entityIndex.'].activity['.$index.']',
                        'message' => 'Exercise not found => '.$id
                    ];
                    continue;
                }
            }
            $activity->setName($item['name'] ?? '');
            $activity->setDescription($item['description'] ?? null);
            $activity->setObjective($item['objective'] ?? null);
            $activity->setInstruction($item['instruction'] ?? null);
            $activity->setDuration($item['duration'] ?? null);
            $activity->setHydration($item['hydration'] ?? false);
            $violations = $this->validator->validate($activity, groups:['Default', $group]);
            if (count($violations) > 0) {
                /** @var ConstraintViolationInterface $violation */
                foreach($violations as $violation) {
                    $errors[] = [
                        'path' => sprintf(
                            'blocks[%d].activities[%d].%s',
                            $entityIndex,
                            $index,
                            $violation->getPropertyPath()
                        ),
                        'message' => $violation->getMessage(),
                    ];
                }
            } else {
                $exercies = ($item['exercises']) ?? [];
                /** @var ErrorResolver $errorResolver */
                $errorResolver = $this->exerciseResolver->resolve($exercies, $activity, $index);
                if ($errorResolver->isValid() === false) {
                    $errors = array_merge($errors, $errorResolver->errors);
                } else {
                    $entity->addActivity($activity);
                }

            }
        }
        return new ErrorResolver($entity, $errors);
    }
}
