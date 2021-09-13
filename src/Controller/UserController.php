<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\Post;
use App\Entity\Role;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function PHPSTORM_META\type;

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
        $list['claims'] = [];
        $i = 0;
        foreach ($user->getRoles() as $role) {
            if ($role->getDeletedAt() == null) {
                $list['roles'][$i] = $role->getType();
                $i++;
            }
        }
        $i = 0;
        foreach ($user->getClaims() as $claim) {
            $list['claims'][$i]['description'] = $claim->getDescription();
            $list['claims'][$i]['created_at'] = $claim->getCreatedAt();
            $list['claims'][$i]['updated_at'] = $claim->getUpdatedAt();
            $list['claims'][$i]['state'] = $claim->getState();
            $list['claims'][$i]['images'] = $claim->getImages();
            $i++;
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

    /*
{
    "login":"M1",
    "firstName":"M1",
    "lastName":"M1",
    "email":"M1",
    "adress":"M1",
    "tel":"M1",
    "password":"M1",
    "expo_id":"M1"
}
*/
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

        $expo_id = $data['expo_id'];

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(['login' => $login]);
        if ($user != null) {
            return $this->json(['message' => 'exist']);
        } else {
            $role = $this->em
                ->getRepository(Role::class)
                ->findOneBy(['type' => 'USER']);
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

            return $this->json([
                'message' => 'success',
                'user' => $this->returnUser($user),
            ]);
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
                return $this->json([
                    'message' => 'success',
                    'user' => $this->returnUser($user),
                ]);
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
        return $this->json([
            'message' => 'success',
            'user' => $this->returnUser($user),
        ]);
    }

    /**
     * @Route("/setRole", name="role_manage",methods={"POST"})
     */
    public function ManageRole(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);
            $user = $this->em->getRepository(User::class)->find($data['id']);
            $role = $this->em
                ->getRepository(Role::class)
                ->findOneBy(['type' => $data['type']]);

            if ($data['action'] == 'remove') {
                $user->removeRole($role);
            } else {
                $user->addRole($role);
            }
            $this->em->persist($user);
            $this->em->flush();
            return $this->json([
                'message' => 'success',
                'user' => $this->returnUser($user),
            ]);
        } catch (\Throwable $th) {
            return new Response($th->getMessage());
        }
    }

    /**
     * @Route("/delete", name="delete_user",methods={"DELETE"})
     */
    public function DeleteUser(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $user = $this->em->getRepository(User::class)->find($data['id']);
        $user->setDeletedAt(new DateTimeImmutable());
        $this->em->persist($user);
        $this->em->flush();

        return $this->json([
            'message' => 'success',
            'user' => $this->returnUser($user),
        ]);
    }

    /**
     * @Route("/truncate", name="truncate")
     */
    public function delete(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);
            if (isset($data['user_id'])) {
                $user = $this->em
                    ->getRepository(User::class)
                    ->find($data['user_id']);
                $this->em->remove($user);
            } else {
                $users = $this->em->getRepository(User::class)->findAll();
                foreach ($users as $user) {
                    $this->em->remove($user);
                }
            }
            if (isset($data['role_id'])) {
                $role = $this->em
                    ->getRepository(Role::class)
                    ->find($data['role_id']);
                $this->em->remove($role);
            } else {
                $roles = $this->em->getRepository(Role::class)->findAll();
                foreach ($roles as $role) {
                    $this->em->remove($role);
                }
            }
            if (isset($data['post_id'])) {
                $post = $this->em
                    ->getRepository(Post::class)
                    ->find($data['post_id']);
                $this->em->remove($post);
            } else {
                $posts = $this->em->getRepository(Post::class)->findAll();
                foreach ($posts as $post) {
                    $this->em->remove($post);
                }
            }

            if (isset($data['claim_id'])) {
                $claim = $this->em
                    ->getRepository(Claim::class)
                    ->find($data['claim_id']);
                $this->em->remove($claim);
            } else {
                $claims = $this->em->getRepository(Claim::class)->findAll();
                foreach ($claims as $claim) {
                    $this->em->remove($claim);
                }
            }
            $this->em->flush();
            return new Response('deleted');
        } catch (\Throwable $th) {
            return new Response($th);
        }
    }

    /**
     * @Route("/", name="update_user",methods={"PUT"})
     */
    public function FunctionName(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);

            $firstName = $data['firstName'];

            $lastName = $data['lastName'];

            $email = $data['email'];

            $adress = $data['adress'];

            $tel = $data['tel'];

            $login = $data['login'];

            $password = md5($data['password']);

            $user = $this->em
                ->getRepository(User::class)
                ->findOneBy(['login' => $login]);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);
            $user->setAdress($adress);
            $user->setTel($tel);
            $user->setLogin($login);
            if ($data['password'] != '') {
                $user->setPassword($password);
            }

            $user->setDeletedAt(null);

            $this->em->persist($user);
            $this->em->flush();

            return $this->json([
                'message' => 'success',
                'user' => $this->returnUser($user),
            ]);
        } catch (\Throwable $th) {
            return $this->json(['message' => 'error']);
        }
    }
}
