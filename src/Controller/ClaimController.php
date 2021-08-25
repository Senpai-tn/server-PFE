<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClaimController extends AbstractController
{
    /**
     * @Route("/claim", name="claim")
     */
    public function index(): Response
    {
        return $this->render('claim/index.html.twig', [
            'controller_name' => 'ClaimController',
        ]);
    }
}
