<?php

namespace App\Service;

use App\Entity\Exercise;
use App\Form\ExerciseType;
use App\Repository\ExerciseRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class ExerciseResolver
{
    public function __construct(
        private readonly ExerciseRepository $exerciseRepository,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @param array<mixed> $data
     * @return Exercise[]
     */
    public function resolve(array $data): array
    {
        $exercises = [];
        $errors = [];

        foreach ($data as $index => $item) {
            if (!is_array($item)) {
                $errors[$index][] = 'Un exercice doit être un objet.';
                continue;
            }

            // Exercice existant
            if (array_key_exists('id', $item)) {
                if (!is_int($item['id']) || $item['id'] <= 0) {
                    $errors[$index][] = 'Identifiant invalide.';
                    continue;
                }

                // Un ID désigne une association, pas une modification.
                if (count($item) !== 1) {
                    $errors[$index][] =
                        'Un exercice existant doit contenir uniquement son id.';
                    continue;
                }

                $exercise = $this->exerciseRepository->find($item['id']);

                if ($exercise === null) {
                    $errors[$index][] =
                        sprintf('Exercice %d introuvable.', $item['id']);
                    continue;
                }

                $exercises[$item['id']] = $exercise;
                continue;
            }

            // Nouvel exercice
            $exercise = new Exercise();

            $form = $this->formFactory->create(
                ExerciseType::class,
                $exercise,
                [
                    'csrf_protection' => false,
                    'validation_groups' => ['Default', 'exercise:create'],
                ]
            );

            $form->submit($item, true);

            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $errors[$index][] = sprintf(
                        '%s : %s',
                        $error->getOrigin()?->getName() ?? 'exercice',
                        $error->getMessage()
                    );
                }

                continue;
            }

            $exercises[] = $exercise;
        }

        if ($errors !== []) {
            throw new UnprocessableEntityHttpException(
                json_encode($errors, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            );
        }

        return array_values($exercises);
    }
}
