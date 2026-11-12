<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\Api\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users', name: 'api_users_')]
final class UserController extends AbstractController
{

  public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory
    )
    {
    }

    #[Route('', name: 'list', methods:['GET'])]
    #[IsGranted(User::ROLE_ADMIN)]
    #[OA\Get(
        path: '/api/users',
        summary: 'Liste des utilisateurs',
        tags:['Utilisateurs'],
        responses: [
            new OA\Response(
                response:200,
                description: 'Liste des utilisateurs',
                content: new Model(type:User::class, groups:['user:detail'])
            )
        ]
    )]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        return $this->json($users, JsonResponse::HTTP_OK, context:['groups' => ['user:detail']]);
    }

    #[Route('', name: 'create', methods:['POST'])]
    #[IsGranted(User::ROLE_ADMIN)]
    #[OA\Post(
        path: '/api/users',
        summary: 'Créer un utilisateurs',
        tags:['Utilisateurs'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:RegistrationFormType::class)
        ),
        responses: [
            new OA\Response(
                response:201,
                description: 'Created',
                content: new Model(type:User::class, groups:['user:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function create(Request $request, UserPasswordHasherInterface $userPassordHasher): JsonResponse
    {
        $user = new User();
        $formUser = $this->formFactory->create(RegistrationFormType::class, $user, [
            'validation_groups' => ['Default', 'user:create'],
        ]);
        $userData = json_decode($request->getContent(), true);
        $formUser->submit($userData, false);
        if ($formUser->isValid() === true) {
            $user->setPassword($userPassordHasher->hashPassword($user, $user->getPlainPassword()));
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $response = $this->json($user, JsonResponse::HTTP_CREATED, context:['groups' => ['user:detail']]);
        } else {
            $response = $this->json($formUser->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    #[Route('/{id}', name: 'update', methods:['PATCH'])]
    #[IsGranted(User::ROLE_ADMIN)]
    #[OA\Patch(
        path: '/api/users/{id}',
        summary: 'Mettre à jour un utilisateur',
        tags:['Utilisateurs'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new Model(type:RegistrationFormType::class)
        ),
        responses: [
            new OA\Response(
                response:202,
                description: 'Mise à jour',
                content: new Model(type:User::class, groups:['user:detail'])
            ),
             new OA\Response(
                response:400,
                description: 'Erreur de données',
            )
        ]
    )]
    public function update(User $user, Request $request, UserPasswordHasherInterface $userPassordHasher): JsonResponse
    {
        $formUser = $this->formFactory->create(RegistrationFormType::class, $user, [
            'validation_groups' => ['Default', 'user:update'],
        ]);
        $jsonContent = json_decode($request->getContent(), true);
        $updatePass = false;
        if (isset($jsonContent['password']) === true) {
            $updatePass = true;
        }
        $formUser->submit($jsonContent, false);
        if ($formUser->isValid() === true) {
            if($updatePass === true) {
                $user->setPassword($userPassordHasher->hashPassword($user, $user->getPassword()));
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $response = $this->json($user, JsonResponse::HTTP_ACCEPTED, context:['groups' => ['user:detail']]);
        } else {
            $response = $this->json($formUser->getErrors(true), JsonResponse::HTTP_BAD_REQUEST);
        }
        return $response;
    }

    #[Route('/{id}', name: 'delete', methods:['DELETE'])]
    #[IsGranted(User::ROLE_ADMIN)]
    #[OA\Delete(
        path: '/api/users/{id}',
        summary: 'Supprimer un utilisateur',
        tags:['Utilisateurs'],
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
    public function delete(User $user): JsonResponse
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->json(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
