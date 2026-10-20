<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SiteController extends AbstractController
{
    #[Route('', name: 'app_site')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function index(): Response
    {
       return  $this->redirectToRoute('app_session_index');
    }
}
