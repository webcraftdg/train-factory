<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name:'app_')]
final class SiteController extends AbstractController
{
    #[Route(
        '/{path}',
        name: 'site',
        requirements: [
            'path' => '^(?!(?:api|bo|angular)(?:/|$)).*'
        ],
        defaults: ['path' => ''],
        priority: -100,
        methods: ['GET']
    )]
    #[PageTitle(title:'Accueil')]

    public function index(): Response
    {
        return $this->render('site/index.html.twig');
    }
}
