<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/", name="user")
     */
    public function index(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);

        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setFirstName('Khaled');
        $em->persist($user);
        $em->flush();
        $users = $em->getRepository(User::class)->findAll();
        return new Response(count($users) . ' users' . $data['id']);
    }
}
