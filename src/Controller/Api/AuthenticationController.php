<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\JsonContent;
final class AuthenticationController extends AbstractController
{
    /**
     * login
     *
     * @return void
     */
    #[Route('/api/login', name: 'app_authentication_login', methods:['POST'])]
    #[OA\Post(
        path:'/api/login',
        summary:'Connxion utilisateur',
        tags:['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response:200,
                description: 'Connexion',
                content:new JsonContent(
                    type:'array',
                    items:new OA\Items(
                        properties: [
                            new OA\Property(property:'token', type:'string')
                        ]
                    )
                )
            )
        ]
    )]
    public function login()
    {}
}

