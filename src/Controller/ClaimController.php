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
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @Route("/claim")
 */
class ClaimController extends AbstractController
{
    private $em;
    private $client;
    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $client
    ) {
        $this->em = $em;
        $this->client = $client;
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
        $list['user']['id'] = $claim->getUser()->getId();
        $list['user']['firstName'] = $claim->getUser()->getFirstName();
        $list['user']['lastName'] = $claim->getUser()->getLastName();
        $list['user']['email'] = $claim->getUser()->getEmail();
        $list['user']['adress'] = $claim->getUser()->getAdress();
        $list['user']['tel'] = $claim->getUser()->getTel();
        $list['user']['login'] = $claim->getUser()->getLogin();
        $list['coord']['latitude'] = $claim->getLatitude();
        $list['coord']['longitude'] = $claim->getLongitude();

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
            $claims = $this->em->getRepository(Claim::class)->findAll();
            $list = [];
            foreach ($claims as $key => $claim) {
                $list[$key] = $this->returnClaim($claim);
            }
            return $this->json(['message' => 'success', 'claim' => $list]);
        }
    }

    /**
     * @Route("/", name="add_claim" ,methods={"POST"})
     */
    public function Add(Request $r): Response
    {
        try {
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
            $user = $this->em
                ->getRepository(User::class)
                ->find($r->request->get('user_id'));
            $claim = new Claim();
            $claim->setLatitude($r->request->get('latitude'));
            $claim->setLongitude($r->request->get('longitude'));
            $claim->setCreatedAt(new DateTimeImmutable());
            $claim->setDescription($r->request->get('description'));
            $claim->setLongitude($r->request->get('longitude'));
            $claim->setLatitude($r->request->get('latitude'));
            $claim->setState('sent');
            $claim->setImages($images);
            $claim->setUser($user);
            $this->em->persist($claim);
            $this->em->flush();
            return $this->json([
                'message' => 'success',
                'claim' => $this->returnClaim($claim),
            ]);
        } catch (\Throwable $th) {
            return $this->json(['message' => $th->getMessage()]);
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
            if ($data['state'] == 'accepted' || $data['state'] == 'refused') {
                $response = $this->client->request(
                    'POST',
                    'https://exp.host/--/api/v2/push/send',
                    [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                            'Accept-encoding' => 'gzip, deflate',
                        ],
                        'body' => json_encode([
                            'to' => $claim->getUser()->getExpoId(),
                            'sound' => 'default',
                            'title' => 'Your claim was ' . $data['state'],
                            'body' =>
                                'An admin has ' .
                                $data['state'] .
                                ' your claim',
                            'data' => ['someData' => 'goes here'],
                        ]),
                    ]
                );
            }
            return $this->json([
                'message' => 'success',
                'claim' => $this->returnClaim($claim),
            ]);
        } catch (\Throwable $th) {
        }
    }
}
