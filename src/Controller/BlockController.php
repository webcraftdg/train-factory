<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\Block;
use App\Entity\User;
use App\Form\BlockType;
use App\Repository\BlockRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/block', name:'app_block_')]
#[IsGranted('IS_AUTHENTICATED')]
final class BlockController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    #[PageTitle(
        title:'Liste des blocs',
        section:'Blocs'
    )]
    public function index(BlockRepository $blockRepository): Response
    {
        return $this->render('block/index.html.twig', [
            'blocks' => $blockRepository->createQueryFindWithConditions()->getQuery()->getResult(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[PageTitle(
        title:'Créer un bloc',
        section:'Blocs'
    )]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $block = new Block();
        $form = $this->createForm(BlockType::class, $block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($block);
            $entityManager->flush();

            return $this->redirectToRoute('app_block_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('block/new.html.twig', [
            'block' => $block,
            'form' => $form,
            'action' => $this->generateUrl('app_block_new')
        ]);
    }

    #[Route('/async-creer', name: 'async_new', methods: ['GET', 'POST'])]
    public function newAsync(Request $request, EntityManagerInterface $entityManager): Response
    {
        $activity = new Block();
        $form = $this->createForm(BlockType::class, $activity);
        $form->handleRequest($request);
        $response = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($activity);
            $entityManager->flush();
            $response = $this->json($activity, JsonResponse::HTTP_CREATED, context:['groups' => ['activity:create']]);
        }
        if ($response === null) {
            $response =  $this->render('components/_form-block.html.twig', [
                'form' => $form,
                'activeCreate' => false,
                'action' => $this->generateUrl('app_block_async_new')
            ]);
        }

        return $response;
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[PageTitle(
        title:'Détail du bloc',
        section:'Blocs'
    )]
    public function show(Block $block): Response
    {
        return $this->render('block/show.html.twig', [
            'block' => $block,
        ]);
    }

    #[Route('/{id}/asyn-detail', name: 'async_show', methods: ['GET'])]
    public function asyncShow(Block $block): Response
    {
        return $this->render('components/_block_item.html.twig', [
            'bloc' => $block,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[PageTitle(
        title:'Editer un bloc',
        section:'Blocs'
    )]
    public function edit(Request $request, Block $block, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlockType::class, $block);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_block_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('block/edit.html.twig', [
            'block' => $block,
            'form' => $form,
            'action' => $this->generateUrl('app_block_edit', ['id' => $block->getId()])
        ]);
    }


    #[Route('/{id}/archiver', name: 'archived', methods: ['GET'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function archived(Block $block, EntityManagerInterface $entityManager): Response
    {
        $block->setDateArchived(new DateTime());
        $entityManager->persist($block);
        $entityManager->flush();
        return $this->redirectToRoute('app_block_index', [], Response::HTTP_SEE_OTHER);
    }
}
