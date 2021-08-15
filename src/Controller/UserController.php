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
     * @Route("/", name="list_users",methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();
        $list = [];
        foreach ($users as $key => $user) {
            $list[$key]['id'] = $user->getId();
            $list[$key]['firstName'] = $user->getFirstName();
        }
        return new Response(json_encode($list));
    }
}
