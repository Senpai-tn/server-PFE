<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
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
            $list[$key]['lastName'] = $user->getLastName();
            $list[$key]['email'] = $user->getEmail();
            $list[$key]['adress'] = $user->getAdress();
            $list[$key]['tel'] = $user->getTel();
            $list[$key]['login'] = $user->getLogin();
            $list[$key]['password'] = $user->getPassword();
            $list[$key]['expo_id'] = $user->getExpoId();
            foreach ($user->getRoles() as $index => $role) {
                $list[$key]['roles'][$index] = $role->getType();
            }
            $list[$key]['created_at'] = $user->getCreatedAt();
            $list[$key]['deleted_at'] = $user->getDeletedAt();
        }
        return $this->json($list);
    }

    /**
     * @Route("/register", name="register",methods={"POST"})
     */
    public function Register(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $firstName = '';

        $lastName = '';

        $email = '';

        $adress = '';

        $tel = '';

        $login = 'K';

        $password = md5('55');

        $expo_id = '';

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['login' => $login]);

        if ($user != null) {
            return $this->json(['message' => 'exist']);
        } else {
            $role = $this->em->getRepository(Role::class)->find(5);
            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setAdress($adress);
            $user->setTel($tel);
            $user->setLogin($login);
            $user->setPassword($password);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setDeletedAt(null);
            $user->setExpoId($expo_id);
            $user->addRole($role);
            $this->em->persist($user);
            $this->em->flush();
            $list['id'] = $user->getId();
            $list['firstName'] = $user->getFirstName();
            $list['lastName'] = $user->getLastName();
            $list['email'] = $user->getEmail();
            $list['adress'] = $user->getAdress();
            $list['tel'] = $user->getTel();
            $list['login'] = $user->getLogin();
            $list['password'] = $user->getPassword();
            $list['expo_id'] = $user->getExpoId();
            foreach ($user->getRoles() as $index => $role) {
                $list['roles'][$index] = $role->getType();
            }
            $list['created_at'] = $user->getCreatedAt();
            $list['deleted_at'] = $user->getDeletedAt();
            return $this->json($list);
        }
    }

    /**
     * @Route("/login", name="login",methods={"POST"})
     */
    public function Login(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        return $this->json(['message' => 'exist']);
    }
}
