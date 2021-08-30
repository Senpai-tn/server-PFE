<?php

namespace App\Controller;

use App\Entity\Claim;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/claim")
 */
class ClaimController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    function returnClaim($claim)
    {
        $list = [];

        $list['id'] = $claim->getId();
        $list['description'] = $claim->getDescription();
        $list['created_at'] = $claim->getCreatedAt();
        $list['updated_at'] = $claim->getUpdatedAt();
        $list['state'] = $claim->getState();
        $list['images'] = $claim->getImages();
        return $list;
    }

    /**
     * @Route("/", name="claim" ,methods={"GET"})
     */
    public function index(Request $r): Response
    {
        if ($r->query->get('user_id') != null) {
            $claims = $this->em
                ->getRepository(Claim::class)
                ->findBy(['user' => $r->query->get('user_id')]);
            $list = [];
            foreach ($claims as $key => $claim) {
                $list[$key] = $this->returnClaim($claim);
            }
            return $this->json(['message' => 'success', 'claim' => $list]);
        } else {
            return $this->render('claim/index.html.twig', [
                'controller_name' => 'ClaimController',
            ]);
        }
    }

    /**
     * @Route("/", name="add_claim" ,methods={"POST"})
     */
    public function Add(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);
            $user = $this->em
                ->getRepository(User::class)
                ->find($data['user_id']);
            $claim = new Claim();
            $claim->setCreatedAt(new DateTimeImmutable());
            $claim->setDescription($data['description']);
            $files = [];
            $i = 1;
            while ($r->files->get('file' . $i) != null) {
                $files[$i] = $r->files->get('file' . $i);
                $i++;
            }
            $images = [];
            foreach ($files as $file) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                array_push($images, $filename);
                $file->move($this->getParameter('images_directory'), $filename);
                unset($filename);
            }

            $claim->setImages($images);
            $claim->setState('Sent');
            $claim->setUser($user);
            $this->em->persist($claim);
            $this->em->flush();
            return new Response('test');
        } catch (\Throwable $th) {
        }
    }

    /**
     * @Route("/", name="update_claim" ,methods={"PUT"})
     */
    public function Update(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);
            $claim = $this->em->getRepository(Claim::class)->find($data['id']);
            $claim->setState($data['state']);
            $claim->setUpdatedAt(new DateTimeImmutable());
            $this->em->persist($claim);
            $this->em->flush();
            $list = $this->returnClaim($claim);
            return $this->json(['message' => 'success', 'claim' => $list]);
        } catch (\Throwable $th) {
        }
    }
}
