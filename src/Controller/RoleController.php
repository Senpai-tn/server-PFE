<?php

namespace App\Controller;

use App\Entity\Role;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/role")
 */
class RoleController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    function returnRole($role)
    {
        $list = [];
        $list['id'] = $role->getId();
        $list['type'] = $role->getType();
        $list['deleted_at'] = $role->getDeletedAt();
        return $list;
    }

    /**
     * @Route("/", name="role",methods={"GET"})
     */
    public function index(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        if (isset($data['id'])) {
            $role = $this->em->getRepository(Role::class)->find($data['id']);
            return $this->json([
                'message' => 'success',
                'role' => $this->returnRole($role),
            ]);
        } elseif ($data['deleted'] == false) {
            $roles = $this->em
                ->getRepository(Role::class)
                ->findBy(['deleted_at' => null]);
        } else {
            $roles = $this->em->getRepository(Role::class)->findAll();
        }
        $list = [];
        foreach ($roles as $key => $role) {
            $list[$key] = $this->returnRole($role);
        }
        return $this->json(['message' => 'success', 'roles' => $list]);
    }

    /**
     * @Route("/", name="add_role",methods={"POST"})
     */
    public function Add(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $role = new Role();
        $role->setType($data['type']);
        $this->em->persist($role);
        $this->em->flush();
        return $this->json([
            'message' => 'success',
            'role' => $this->returnRole($role),
        ]);
    }

    /**
     * @Route("/", name="update_role",methods={"PUT"})
     */
    public function Update(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $role = $this->em->getRepository(Role::class)->find($data['id']);
        $role->setType($data['type']);
        $role->setDeletedAt(null);
        $this->em->persist($role);
        $this->em->flush();

        return $this->json([
            'message' => 'success',
            'role' => $this->returnRole($role),
        ]);
    }

    /**
     * @Route("/", name="delete_role",methods={"DELETE"})
     */
    public function Delete(Request $r): Response
    {
        $data = json_decode($r->getContent(), true);
        $role = $this->em->getRepository(Role::class)->find($data['id']);
        $role->setDeletedAt(new DateTimeImmutable());
        $this->em->persist($role);
        $this->em->flush();

        return $this->json([
            'message' => 'success',
            'role' => $this->returnRole($role),
        ]);
    }
}
