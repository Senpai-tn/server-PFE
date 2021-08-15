<?php

namespace App\Controller;

use App\Entity\User;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @Route("/", name="list_users",methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();
        $list = [];
        foreach ($users as $key => $user) {
            $list[$key]['id'] = $user->getId();
            $list[$key]['firstName'] = $user->getFirstName();
        }
        return new Response(json_encode($list));
    }

    /**
     * @Route("/", name="register",methods={"POST"})
     */
    public function Register(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $firstName = $data['firstName'];
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['firstName' => $firstName]);
        if ($user != null) {
            return $this->json(['message' => 'exist']);
        } else {
            $user = new User();
            $user->setFirstName($firstName);
            $this->em->persist($user);
            $this->em->flush();
            return $this->json($user);
        }
    }
}
