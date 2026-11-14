<?php

namespace App\Controller;

use App\Attribute\PageTitle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('', name:'app_')]
final class SiteController extends AbstractController
{
    #[Route('/', name: 'site')]
    #[PageTitle(title:'Accueil')]
    public function index(): Response
    {
        return $this->render('site/index.html.twig');
    }
}
