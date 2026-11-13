<?php

namespace App\Controller\Api;

use App\Entity\Block;
use App\Entity\User;
use App\Form\Api\ApiBlockType;
use App\Repository\BlockRepository;
use App\Service\ActivityResolver;
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

#[Route('/api/blocs', name: 'api_blocs_')]
#[IsGranted('IS_AUTHENTICATED')]
final class BlockController extends AbstractController
{

  public function __construct(
        private BlockRepository $blockRepository,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
        private ActivityResolver $activityResolver
    )
    {
    }

    #[Route('', name: 'list', methods:['GET'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Get(
        path: '/api/blocs',
        summary: 'Liste des blocs',
        tags:['Blocs'],
        responses: [
            new OA\Response(
                response:200,
                description: 'Liste des blocs',
                content: new Model(type:ApiBlockType::class, groups:['block:list'])
            )
        ]
    )]
    public function list(): JsonResponse
    {
        $entities = $this->blockRepository->findAll();
        return $this->json($entities, JsonResponse::HTTP_OK, context:['groups' => ['block:list']]);
    }

    #[Route('', name: 'create', methods:['POST'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Post(
        path: '/api/blocs',
        summary: 'Créer un bloc',
        tags:['Blocs'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ApiBlockType::class)
        ),
        responses: [
            new OA\Response(
                response:201,
                description: 'Created',
                content: new Model(type:ApiBlockType::class, groups:['block:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        $entity = new Block();
        $form = $this->formFactory->create(ApiBlockType::class, $entity, [
            'validation_groups' => ['Default', 'block:create'],
        ]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        return $this->manageActivity($form, $data, $entity);
    }

    #[Route('/{id}', name: 'update', methods:['PATCH'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Patch(
        path: '/api/blocs/{id}',
        summary: 'Mettre à jour un bloc',
        tags:['Blocs'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:ApiBlockType::class)
        ),
        responses: [
            new OA\Response(
                response:202,
                description: 'Mise à jour',
                content: new Model(type:ApiBlockType::class, groups:['block:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function update(Block $entity, Request $request): JsonResponse
    {
        $form = $this->formFactory->create(ApiBlockType::class, $entity, [
            'validation_groups' => ['Default', 'block:update'],
        ]);
        $jsonContent = json_decode($request->getContent(), true);
        $form->submit($jsonContent, false);
        return $this->manageActivity($form, $jsonContent, $entity);
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    #[IsGranted(User::ROLE_CREATOR)]
    #[OA\Delete(
        path: '/api/blocs/{id}',
        summary: 'Supprimer un bloc',
        tags:['Blocs'],
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
    public function delete(Block $entity): JsonResponse
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function manageActivity(FormInterface $form, array $data, Block $entity) : Response
    {
        if ($form->isValid() === true) {
            $this->entityManager->beginTransaction();
            $activities = ($data['activities']) ?? [];
            /** @var ErrorResolver $errorResolver */
            $errorResolver = $this->activityResolver->resolve(
                data: $activities,
                entity: $entity
            );
            if ($errorResolver->isValid() === false) {
                $response = $this->json($errorResolver->errors, JsonResponse::HTTP_BAD_REQUEST);
                $this->entityManager->rollback();
            } else {
                $this->entityManager->persist($entity);
                $this->entityManager->commit();
                $this->entityManager->flush();
                $response = $this->json($entity, JsonResponse::HTTP_CREATED, context:['groups' => ['block:detail']]);
            }
        } else {
            $response = $this->json($form->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }
}
