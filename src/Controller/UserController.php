<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/", name="user")
     */
    public function index(): Response
    {
        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setFirstName('Khaled');
        $em->persist($user);
        $em->flush();
        $users = $em->getRepository(User::class)->findAll();
        return new Response(count($users) . ' users');
    }
}
