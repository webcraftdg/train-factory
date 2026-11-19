<?php

namespace App\Controller\Api;

use App\Entity\Activity;
use App\Entity\User;
use App\Form\Api\ApiActivityType;
use App\Repository\ActivityRepository;
use App\Service\ErrorResolver;
use App\Service\ExerciseResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/activities', name: 'api_activities_')]
#[IsGranted('IS_AUTHENTICATED')]
final class ActivityController extends AbstractController
{

  public function __construct(
        private ActivityRepository $activityRepository,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
        private ExerciseResolver $exerciseResolver
    )
    {
    }

    #[Route('', name: 'list', methods:['GET'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Get(
        path: '/api/activities',
        summary: 'Liste des activités',
        tags:['Activities'],
        responses: [
            new OA\Response(
                response:200,
                description: 'Liste des exercices',
                content: new Model(type:ApiActivityType::class, groups:['activity:list'])
            )
        ]
    )]
    public function list(): JsonResponse
    {
        $entities = $this->activityRepository->findAll();
        return $this->json($entities, JsonResponse::HTTP_OK, context:['groups' => ['activity:list']]);
    }

    #[Route('', name: 'create', methods:['POST'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Post(
        path: '/api/activities',
        summary: 'Créer un activité',
        tags:['Activities'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ApiActivityType::class)
        ),
        responses: [
            new OA\Response(
                response:201,
                description: 'Created',
                content: new Model(type:ApiActivityType::class, groups:['activity:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $entity = new Activity();
        $form = $this->formFactory->create(ApiActivityType::class, $entity, [
            'validation_groups' => ['Default', 'activity:create'],
        ]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        return $this->manageActivity($form, $entity);
    }

    #[Route('/{id}', name: 'update', methods:['PATCH'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Patch(
        path: '/api/activities/{id}',
        summary: 'Mettre à jour un activité',
        tags:['Activities'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ApiActivityType::class)
        ),
        responses: [
            new OA\Response(
                response:202,
                description: 'Mise à jour',
                content: new Model(type:ApiActivityType::class, groups:['activity:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function update(Activity $entity, Request $request): JsonResponse
    {
        $form = $this->formFactory->create(ApiActivityType::class, $entity, [
            'validation_groups' => ['Default', 'activity:update'],
        ]);
        $jsonContent = json_decode($request->getContent(), true);
        $form->submit($jsonContent, false);
        return $this->manageActivity($form, $entity);
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Delete(
        path: '/api/activities/{id}',
        summary: 'Supprimer un activité',
        tags:['Activities'],
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
    public function delete(Activity $entity): JsonResponse
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function manageActivity(FormInterface $form, Activity $entity) : Response
    {
        if ($form->isValid() === true) {
            $this->entityManager->beginTransaction();
            $exercicesData = $form->get('exercises')->getData();
            /** @var ErrorResolver $errorResolver */
            $errorResolver = $this->exerciseResolver->resolve(
                data: $exercicesData,
                entity: $entity
            );
            if ($errorResolver->isValid() === false) {
                $response = $this->json($errorResolver->errors, JsonResponse::HTTP_BAD_REQUEST);
                $this->entityManager->rollback();
            } else {
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                $this->entityManager->commit();
                $response = $this->json($entity, JsonResponse::HTTP_CREATED, context:['groups' => ['activity:detail']]);
            }
        } else {
            $response = $this->json($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }
}
