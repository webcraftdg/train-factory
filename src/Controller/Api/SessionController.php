<?php

namespace App\Controller\Api;

use App\Entity\Session;
use App\Entity\User;
use App\Form\Api\ApiSessionType;
use App\Repository\SessionRepository;
use App\Service\Api\FormErrorFormatter;
use App\Service\BlockResolver;
use App\Service\ErrorResolver;
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

#[Route('/api/sessions', name: 'api_sessions_')]
#[IsGranted('IS_AUTHENTICATED')]
final class SessionController extends AbstractController
{

  public function __construct(
        private SessionRepository $sessionRepository,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
        private BlockResolver $blockResolver,
        private FormErrorFormatter $formErrorFormatter
    )
    {
    }

    #[Route('', name: 'list', methods:['GET'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Get(
        path: '/api/sessions',
        summary: 'Liste des séances',
        tags:['Sessions'],
        responses: [
            new OA\Response(
                response:200,
                description: 'Liste des séances',
                content: new Model(type:ApiSessionType::class, groups:['session:detail'])
            )
        ]
    )]
    public function list(): JsonResponse
    {
        $entities = $this->sessionRepository->findAll();
        return $this->json($entities, JsonResponse::HTTP_OK, context:['groups' => ['session:detail']]);
    }

    #[Route('', name: 'create', methods:['POST'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Post(
        path: '/api/sessions',
        summary: 'Créer une séance',
        tags:['Sessions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ApiSessionType::class)
        ),
        responses: [
            new OA\Response(
                response:201,
                description: 'Created',
                content: new Model(type:ApiSessionType::class, groups:['session:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $entity = new Session();
        $form = $this->formFactory->create(ApiSessionType::class, $entity, [
            'validation_groups' => ['Default', 'session:create'],
        ]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        return $this->manageActivity($form, $data, $entity);
    }

    #[Route('/{id}', name: 'update', methods:['PATCH'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Patch(
        path: '/api/sessions/{id}',
        summary: 'Mettre à jour une séance',
        tags:['Sessions'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ApiSessionType::class)
        ),
        responses: [
            new OA\Response(
                response:202,
                description: 'Mise à jour',
                content: new Model(type:ApiSessionType::class, groups:['session:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function update(Session $entity, Request $request): JsonResponse
    {
        $form = $this->formFactory->create(ApiSessionType::class, $entity, [
            'validation_groups' => ['Default', 'session:update'],
        ]);
        $jsonContent = json_decode($request->getContent(), true);
        $form->submit($jsonContent, false);
        return $this->manageActivity($form, $jsonContent, $entity);
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Delete(
        path: '/api/sessions/{id}',
        summary: 'Supprimer une séance',
        tags:['Sessions'],
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
    public function delete(Session $entity): JsonResponse
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'get', methods:['GET'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Get(
        path: '/api/sessions/{id}',
        summary: 'Lit une séance',
        tags:['Sessions'],
        responses: [
            new OA\Response(
                response:200,
                description: 'Lecture',
                content: new Model(type:ApiSessionType::class, groups:['session:detail'])
            ),
             new OA\Response(
                response:404,
                description: 'Non trouvé',
            )
        ]
    )]
    public function get(Session $entity): JsonResponse
    {
        return $this->json($entity, JsonResponse::HTTP_CREATED, context:['groups' => ['session:detail']]);
    }

    private function manageActivity(FormInterface $form, array $data, Session $entity) : Response
    {
        if ($form->isValid() === true) {
            $this->entityManager->beginTransaction();
            $blocks = ($data['blocks']) ?? [];
            /** @var ErrorResolver $errorResolver */
            $errorResolver = $this->blockResolver->resolve(
                data: $blocks,
                entity: $entity
            );
            if ($errorResolver->isValid() === false) {
                $response = $this->json($errorResolver->errors, JsonResponse::HTTP_BAD_REQUEST);
                $this->entityManager->rollback();
            } else {
                $this->entityManager->persist($entity);
                $this->entityManager->commit();
                $this->entityManager->flush();
                $response = $this->json($entity, JsonResponse::HTTP_CREATED, context:['groups' => ['session:detail']]);
            }
        } else {
            $response = $this->json(['errors' => $this->formErrorFormatter->format($form)], JsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }
}
