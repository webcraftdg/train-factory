<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\User;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/activites', name:'app_activity_')]
#[IsGranted('IS_AUTHENTICATED')]
final class ActivityController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(ActivityRepository $activityRepository): Response
    {
        return $this->render('activity/index.html.twig', [
            'activities' => $activityRepository->createQueryFindWithConditions()->getQuery()->getResult(),
        ]);
    }

    #[Route('/creer', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);
        $order = json_decode(
            $form->get('exerciseOrder')->getData() ?: '[]',
            true
        );

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setExerciseOrder($order);
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('app_activity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activity/new.html.twig', [
            'activity' => $activity,
            'form' => $form,
            'action' => $this->generateUrl('app_activity_new')
        ]);
    }

    #[Route('/{id}/detail', name: 'show', methods: ['GET'])]
    public function show(Activity $activity): Response
    {
        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/{id}/asyn-detail', name: 'async_show', methods: ['GET'])]
    public function asyncShow(Activity $activity): Response
    {
        return $this->render('components/_activity_item.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Activity $activity, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(
            ActivityType::class,
            $activity
        );
        $form->handleRequest($request);
        $order = json_decode(
            $form->get('exerciseOrder')->getData() ?: '[]',
            true
        );

        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setExerciseOrder($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_activity_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form,
            'action' => $this->generateUrl('app_activity_edit', ['id' => $activity->getId()])
        ]);
    }

    #[Route('/async-creer', name: 'async_new', methods: ['GET', 'POST'])]
    public function newAsync(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);
        $response = null;

        $order = json_decode(
            $form->get('exerciseOrder')->getData() ?: '[]',
            true
        );
        if ($form->isSubmitted() && $form->isValid()) {
            $activity->setExerciseOrder($order);
            $entityManager->persist($activity);
            $entityManager->flush();
            $response = $this->json($activity, JsonResponse::HTTP_CREATED, context:['groups' => ['activity:create']]);
        }
        if ($response === null) {
            $response =  $this->render('components/_form-activity.html.twig', [
                'form' => $form,
                'activeCreate' => false,
                'action' => $this->generateUrl('app_activity_async_new')
            ]);
        }

        return $response;
    }

    #[Route('/{id}/archiver', name: 'archived', methods: ['GET'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function archived(Activity $activity, EntityManagerInterface $entityManager): Response
    {
        $activity->setDateArchived(new DateTime());
        $entityManager->persist($activity);
        $entityManager->flush();
        return $this->redirectToRoute('app_activity_index', [], Response::HTTP_SEE_OTHER);
    }
}
