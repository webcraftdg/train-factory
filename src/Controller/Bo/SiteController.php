<?php

namespace App\Controller\Bo;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/bo', name:'bo_')]
final class SiteController extends AbstractController
{
    #[Route('', name: 'site')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function index(): Response
    {
       return  $this->redirectToRoute('bo_session_index');
    }
}
