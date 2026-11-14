<?php

namespace App\Controller\Api;

use App\Datapipeline\Service\ExportSessionService;
use App\Entity\Session;
use App\Entity\User;
use App\Exception\HttpException;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/exports', name: 'api_exports_')]
#[IsGranted('IS_AUTHENTICATED')]
final class ExportController extends AbstractController
{

  public function __construct(
        private SessionRepository $sessionRepository
    )
    {
    }

    #[Route('/sessions/{id}/excel', name: 'excel', methods: ['GET'])]
    #[IsGranted(User::ROLE_TRAINEE)]
    #[OA\Get(
        path: '/api/exports/sessions/{id}/excel',
        summary: 'Exporter une séance',
        tags:['Export'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Téléchargement de la séance au format Excel (.xlsx)',
                content: new OA\MediaType(
                    mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    schema: new OA\Schema(
                        type: 'string',
                        format: 'binary'
                    )
                )
            ),
             new OA\Response(
                response:404,
                description: 'Non trouvé',
            )
        ]
    )]
    public function excel(
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
