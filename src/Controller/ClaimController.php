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
        $data = json_decode($r->getContent(), true);
        if (isset($data['user_id'])) {
            $claims = $this->em
                ->getRepository(Claim::class)
                ->findBy(['user' => $data['user_id']]);
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
            $claim->setDescription('DESC');
            $claim->setImages(['f2cc45d6209c8f6105cb06e18bce9bf4.jpg']);
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
    public function Update(): Response
    {
        return $this->render('$0.html.twig', []);
    }
}
