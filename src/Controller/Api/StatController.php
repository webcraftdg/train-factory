<?php

namespace App\Controller\Api;

use App\Form\Api\ApiStatType;
use App\Repository\ActivityRepository;
use App\Repository\BlockRepository;
use App\Repository\ExerciseRepository;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/stats', name: 'api_stats_')]
#[IsGranted('IS_AUTHENTICATED')]
final class StatController extends AbstractController
{
    /**
     * login
     *
     * @return void
     */
    #[Route('', name: 'index', methods:['GET'])]
    #[OA\Get(
        path:'/api/stats',
        summary:'Statistic',
        tags:['Statistic'],
        responses: [
            new OA\Response(
                response:200,
                description: 'Statistiques',
                content: new Model(type:ApiStatType::class)
            )
        ]
    )]
    public function index(
        SessionRepository $sessionRepository,
        BlockRepository $blockRepository,
        ExerciseRepository $exerciseRepository
    ): JsonResponse {
        return $this->json([
            'nbSessions' => $sessionRepository->count(),
            'nbExercises' => $exerciseRepository->count(),
            'nbBlocks' => $blockRepository->count(),
        ]);
    }
}

