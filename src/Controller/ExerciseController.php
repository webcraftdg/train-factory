<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\Exercise;
use App\Entity\User;
use App\Form\ExerciseType;
use App\Repository\ExerciseRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/exercices', name:'app_exercise_')]
#[IsGranted('IS_AUTHENTICATED')]
final class ExerciseController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    #[PageTitle(
        title:'Liste des exercices',
        section:'Exercices'
    )]
    public function index(ExerciseRepository $exerciseRepository): Response
    {
        return $this->render('exercise/index.html.twig', [
            'exercises' => $exerciseRepository->createQueryFindWithConditions()->getQuery()->getResult(),
        ]);
    }

    #[Route('/creer', name: 'new', methods: ['GET', 'POST'])]
    #[PageTitle(
        title:'Créer un exercice',
        section:'Exercices'
    )]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exercise = new Exercise();
        $form = $this->createForm(ExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exercise);
            $entityManager->flush();

            return $this->redirectToRoute('app_exercise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercise/new.html.twig', [
            'exercise' => $exercise,
            'form' => $form,
            'action' => $this->generateUrl('app_exercise_new')
        ]);
    }

    #[Route('/async-creer', name: 'async_new', methods: ['GET', 'POST'])]
    public function newAsync(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exercise = new Exercise();
        $form = $this->createForm(ExerciseType::class, $exercise);
        $form->handleRequest($request);
        $response = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exercise);
            $entityManager->flush();
            $response = $this->json($exercise, JsonResponse::HTTP_CREATED, context:['groups' => ['exercise:create']]);
        }
        if ($response === null) {
            $response =  $this->render('components/_form-exercices.html.twig', [
                'exercise' => $exercise,
                'form' => $form,
                'action' => $this->generateUrl('app_exercise_async_new')
            ]);
        }

        return $response;
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[PageTitle(
        title:'Editer l\'exercices',
        section:'Exercices'
    )]
    public function edit(Request $request, Exercise $exercise, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ExerciseType::class, $exercise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_exercise_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercise/edit.html.twig', [
            'exercise' => $exercise,
            'form' => $form,
            'action' => $this->generateUrl('app_exercise_edit', ['id' => $exercise->getId()])
        ]);
    }

    #[Route('/{id}/archiver', name: 'archived', methods: ['GET'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function archived(Request $request, Exercise $exercise, EntityManagerInterface $entityManager): Response
    {
        $exercise->setDateArchived(new DateTime());
        $entityManager->persist($exercise);
        $entityManager->flush();
        return $this->redirectToRoute('app_exercise_index', [], Response::HTTP_SEE_OTHER);
    }
}
