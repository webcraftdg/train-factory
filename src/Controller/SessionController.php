<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use App\Entity\Session;
use App\Entity\User;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Datapipeline\Service\ExportSessionService;
use App\Exception\HttpException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[IsGranted('IS_AUTHENTICATED')]
#[Route('/seances', name:'app_session_')]
final class SessionController extends AbstractController
{

    public function __construct(
        private SessionRepository $sessionRepository
    )
    {
    }
    #[Route(name: 'index', methods: ['GET'])]
    #[PageTitle(
        title:'Liste des séances',
        section:'Séances'
    )]
    public function index(SessionRepository $sessionRepository): Response
    {
        return $this->render('session/index.html.twig', [
            'sessions' => $sessionRepository->createQueryFindWithConditions()->getQuery()->getResult(),
        ]);
    }

    #[Route('/create', name: 'new', methods: ['GET', 'POST'])]
      #[PageTitle(
        title:'Créer une séances',
        section:'Séances'
    )]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $session = new Session();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);
        $order = json_decode(
            $form->get('blockOrder')->getData() ?: '[]',
            true
        );
        if ($form->isSubmitted() && $form->isValid()) {
            $session->setBlockOrder($order);
            $entityManager->persist($session);
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/new.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/detail', name: 'show', methods: ['GET'])]
    #[PageTitle(
        title:'Détail de la séances',
        section:'Séances'
    )]
    public function show(Session $session): Response
    {
        return $this->render('session/show.html.twig', [
            'session' => $session,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[PageTitle(
        title:'Editer la séances',
        section:'Séances'
    )]
    public function edit(Request $request, Session $session, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);
        $order = json_decode(
            $form->get('blockOrder')->getData() ?: '[]',
            true
        );
        if ($form->isSubmitted() && $form->isValid()) {
            $session->setBlockOrder($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/archiver', name: 'archived', methods: ['GET'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function archived(Session $session, EntityManagerInterface $entityManager): Response
    {
        $session->setDateArchived(new DateTime());
        $entityManager->persist($session);
        $entityManager->flush();
        return $this->redirectToRoute('app_session_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/exporter', name: 'download', methods: ['GET'])]
    #[IsGranted(User::ROLE_ADMIN)]
    public function download(
        Session $session,
        #[Autowire('%kernel.project_dir%')] string $projectDir
        ): BinaryFileResponse
    {
        $rows = [
            ['id' => $session->getId()]
        ];
        $exportFilePath = $this->createFilePath($projectDir);
        $filename = str_replace(' ', '-', $session->getName());
        $exportService = new ExportSessionService(
            name:$filename.'_export',
            rows: $rows,
            version:$session->getId(),
            stopOnError:true,
            sessionRepository: $this->sessionRepository,
            options: [
                'exportFilePath' => $exportFilePath
            ]
        );
        $report = $exportService->run();
        if ($report->success === true){
            $response = $this->file($exportFilePath, $filename.'.xlsx');
            $response->deleteFileAfterSend(true);
        } else {
             throw new HttpException(message:'Impossible de créer le répertoire d\'export', code:500);
        }
        return $response;
    }

    private function createFilePath(string $target) : string
    {
        try {
             $directory = $target . '/var/exports';
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }
            $tempName =  tempnam($directory, 'session_');
            if( $tempName === false) {
                throw new HttpException(message:'Erreur de création du répertoire', code:500);
            }
            return $tempName;
        } catch(HttpException $e) {
             throw new HttpException(message:'Erreur de création du répertoire', code:500);
        }
    }
}
