<?php

namespace App\Controller\Api;

use App\Entity\Exercise;
use App\Entity\User;
use App\Form\Api\ExerciseType;
use App\Repository\ExerciseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/exercices', name: 'api_exercices_')]
#[IsGranted('IS_AUTHENTICATED')]
final class ExerciseController extends AbstractController
{

  public function __construct(
        private ExerciseRepository $exerciseRepository,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory
    )
    {
    }

    #[Route('', name: 'list', methods:['GET'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Get(
        path: '/api/exercices',
        summary: 'Liste des exercices',
        tags:['Exercices'],
        responses: [
            new OA\Response(
                response:200,
                description: 'Liste des exercices',
                content: new Model(type:User::class, groups:['exercise:detail'])
            )
        ]
    )]
    public function list(): JsonResponse
    {
        $exercices = $this->exerciseRepository->findAll();
        return $this->json($exercices, JsonResponse::HTTP_OK, context:['groups' => ['exercise:detail']]);
    }

    #[Route('', name: 'create', methods:['POST'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Post(
        path: '/api/exercices',
        summary: 'Créer un exercice',
        tags:['Exercices'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ExerciseType::class)
        ),
        responses: [
            new OA\Response(
                response:201,
                description: 'Created',
                content: new Model(type:ExerciseType::class, groups:['exercise:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $entity = new Exercise();
        $form = $this->formFactory->create(ExerciseType::class, $entity, [
            'validation_groups' => ['Default', 'exercise:create'],
        ]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        if ($form->isValid() === true) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $response = $this->json($entity, JsonResponse::HTTP_CREATED, context:['groups' => ['exercise:detail']]);
        } else {
            $response = $this->json($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    #[Route('/{id}', name: 'update', methods:['PATCH'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Patch(
        path: '/api/exercices/{id}',
        summary: 'Mettre à jour un exercice',
        tags:['Exercices'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ExerciseType::class)
        ),
        responses: [
            new OA\Response(
                response:202,
                description: 'Mise à jour',
                content: new Model(type:ExerciseType::class, groups:['exercise:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function update(Exercise $entity, Request $request): JsonResponse
    {
        $form = $this->formFactory->create(ExerciseType::class, $entity, [
            'validation_groups' => ['Default', 'exercise:update'],
        ]);
        $jsonContent = json_decode($request->getContent(), true);
        $form->submit($jsonContent, false);
        if ($form->isValid() === true) {
            $this->entityManager->persist($entity);
            $this->entityManager->flush();
            $response = $this->json($entity, JsonResponse::HTTP_ACCEPTED, context:['groups' => ['exercise:detail']]);
        } else {
            $response = $this->json($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Delete(
        path: '/api/exercices/{id}',
        summary: 'Supprimer un exercise',
        tags:['Exercices'],
        responses: [
            new OA\Response(
                response:204,
                description: 'No content',
            ),
             new OA\Response(
                response:404,
                description: 'Non trouvé',
            )
        ]
    )]
    public function delete(Exercise $entity): JsonResponse
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
