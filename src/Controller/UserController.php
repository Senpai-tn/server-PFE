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

    function returnUser($user)
    {
        $list = [];
        $list['id'] = $user->getId();
        $list['firstName'] = $user->getFirstName();
        $list['lastName'] = $user->getLastName();
        $list['email'] = $user->getEmail();
        $list['adress'] = $user->getAdress();
        $list['tel'] = $user->getTel();
        $list['login'] = $user->getLogin();
        $list['password'] = $user->getPassword();
        $list['expo_id'] = $user->getExpoId();
        $list['roles'] = [];
        $i = 0;
        foreach ($user->getRoles() as $index => $role) {
            if ($role->getDeletedAt() == null) {
                $list['roles'][$i] = $role->getType();
                $i++;
            }
        }
        $list['created_at'] = $user->getCreatedAt();
        $list['deleted_at'] = $user->getDeletedAt();
        return $list;
    }

    /**
     * @Route("/", name="list_users",methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();
        $list = [];
        foreach ($users as $key => $user) {
            $list[$key] = $this->returnUser($user);
        }
        return $this->json(['message' => 'success', 'users' => $list]);
    }

    /**
     * @Route("/register", name="register",methods={"POST"})
     */
    public function Register(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);

        $firstName = $data['firstName'];

        $lastName = $data['lastName'];

        $email = $data['email'];

        $adress = $data['adress'];

        $tel = $data['tel'];

        $login = $data['login'];

        $password = md5($data['password']);

        $expo_id = '';

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['login' => $login]);
        if ($user != null) {
            return $this->json(['message' => 'exist']);
        } else {
            $role1 = new Role();
            $role1->setType('TYPE2');
            $this->em->persist($role1);
            $this->em->flush();

            $role = $this->em->getRepository(Role::class)->find(5);
            $role2 = $this->em->getRepository(Role::class)->find(15);
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
            $user->addRole($role2);
            $this->em->persist($user);
            $this->em->flush();
            $list = $this->returnUser($user);
            return $this->json(['message' => 'success', 'user' => $list]);
        }
    }

    /**
     * @Route("/login", name="login",methods={"POST"})
     */
    public function Login(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $login = $data['login'];
        $password = md5($data['password']);
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['login' => $login]);
        if ($user == null) {
            return $this->json(['message' => 'not exist']);
        } else {
            if ($user->getPassword() != $password) {
                return $this->json(['message' => 'password error']);
            } elseif ($user->getDeletedAt() != null) {
                return $this->json(['message' => 'user blocked']);
            } else {
                $list = $this->returnUser($user);
                return $this->json(['message' => 'success', 'user' => $list]);
            }
        }
    }

    /**
     * @Route("/profile", name="Profile",methods={"GET"})
     */
    public function Profile(Request $r): Response
    {
        $user = $this->em
            ->getRepository(User::class)
            ->find($r->query->get('id'));
        $list = $this->returnUser($user);
        return $this->json(['message' => 'success', 'user' => $list]);
    }

    /**
     * @Route("/setRole", name="role_manage",methods={"POST"})
     */
    public function ManageRole(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $user = $this->em->getRepository(User::class)->find($data['id']);
        $role = $this->em->getRepository(Role::class)->find($data['role_id']);

        if ($data['action'] == 'remove') {
            $user->removeRole($role);
        } else {
            $user->addRole($role);
        }
        $this->em->persist($user);
        $this->em->flush();
        $list = $this->returnUser($user);
        return $this->json(['message' => 'success', 'user' => $list]);
    }

    /**
     * @Route("/delete", name="delete_user",methods={"DELETE"})
     */
    public function DeleteUser(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $user = $this->em->getRepository(User::class)->find($data['id']);
        $user->setDeletedAt(null);
        $this->em->persist($user);
        $this->em->flush();
        $list = $this->returnUser($user);
        return $this->json(['message' => 'success', 'user' => $list]);
    }
}
