<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Exercise;
use App\Repository\ExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class ExerciseResolver
{
    public function __construct(
        private readonly ExerciseRepository $exerciseRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * resolve
     *
     * @param  array                $data
     * @param  \App\Entity\Activity $entity
     *
     * @return array
     */
    public function resolve(array $data, Activity $entity, int $activityIndex = 0): ErrorResolver
    {
        $errors = [];
        foreach($data as $index => $item) {

            $id = ($item['id']) ?? null;
            $group = 'exercise:create';
            if ($id === null) {
                $exercise = new Exercise();
                $this->entityManager->persist($exercise);
            } else {
                $group = 'exercise:update';
                $exercise = $this->exerciseRepository->find($id);
                if ($exercise === null) {
                    $errors[] = [
                        'path' => 'activities['.$activityIndex.'].exercises['.$index.']',
                        'message' => 'Exercise not found => '.$id
                    ];
                }
            }
            $exercise->setName($item['name'] ?? '');
            $exercise->setDescription($item['description'] ?? null);
            $violations = $this->validator->validate($exercise, groups:['Default', $group]);
            if (count($violations) > 0) {
                /** @var ConstraintViolationInterface $violation */
                foreach($violations as $violation) {
                    $errors[] = [
                        'path' => sprintf(
                            'activities[%d].exercises[%d].%s',
                            $activityIndex,
                            $index,
                            $violation->getPropertyPath()
                        ),
                        'message' => $violation->getMessage(),
                    ];
                }
            } else {
                $entity->addExercise($exercise);
            }
        }
        return new ErrorResolver($entity, $errors);
    }
}
