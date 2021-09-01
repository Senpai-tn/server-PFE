<?php

namespace App\Controller;

use App\Entity\Post;
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
 * @Route("/post")
 */
class PostController extends AbstractController
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

    function returnPost($post)
    {
        $list['id'] = $post->getId();
        $list['title'] = $post->getTitle();
        $list['description'] = $post->getDescription();
        $list['images'] = $post->getImages();
        $list['created_at'] = $post->getCreatedAt();
        $list['deleted_at'] = $post->getDeletedAt();
        return $list;
    }

    /**
     * @Route("/", name="posts" , methods={"GET"})
     */
    public function index(Request $r): Response
    {
        try {
            if ($r->query->get('id') == null) {
                $posts = $this->em->getRepository(Post::class)->findAll();
                $list = [];
                foreach ($posts as $key => $post) {
                    $list[$key] = $this->returnPost($post);
                }
                return $this->json(['message' => 'success', 'posts' => $list]);
            } else {
                $post = $this->em
                    ->getRepository(Post::class)
                    ->find($r->query->get('id'));

                return $this->json([
                    'message' => 'success',
                    'post' => $this->returnPost($post),
                ]);
            }
        } catch (\Throwable $th) {
            return $this->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * @Route("/", name="add_post",methods={"POST"})
     */
    public function Add(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);
            $post = new Post();
            $post->setTitle($data['title']);
            $post->setDescription($data['Description']);
            $post->setCreatedAt(new DateTimeImmutable());
            $post->setDeletedAt(null);
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

            $post->setImages($images);
            $this->em->persist($post);
            $this->em->flush();

            $users = $this->em->getRepository(User::class)->findAll();
            foreach ($users as $key => $user) {
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
                            'to' => $user->getExpoId(),
                            'sound' => 'default',
                            'title' => date('H:i:s'),
                            'body' => 'Tudo bem ?',
                            'data' => ['someData' => 'goes here'],
                        ]),
                    ]
                );
            }

            return $this->json([
                'message' => 'success',
                'post' => $this->returnPost($post),
            ]);
        } catch (\Throwable $th) {
            return $this->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * @Route("/", name="update_post",methods={"PUT"})
     */
    public function Update(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);
            $id = $data['id'];
            $title = $data['title'];
            $description = $data['description'];
            $post = $this->em->getRepository(Post::class)->find($id);
            if ($post == null) {
                return $this->json(['message' => 'not found']);
            } else {
                $post->setTitle($title);
                $post->setDescription($description);
                $this->em->persist($post);
                $this->em->flush();

                return $this->json([
                    'message' => 'success',
                    'post' => $this->returnPost($post),
                ]);
            }
        } catch (\Throwable $th) {
            return $this->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * @Route("/", name="delete_post",methods={"DELETE"})
     */
    public function Delete(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);
            $id = $data['id'];
            $post = $this->em->getRepository(Post::class)->find($id);
            if ($post == null) {
                return $this->json(['message' => 'not found']);
            } else {
                $post->setDeletedAt(new DateTimeImmutable());
                $this->em->persist($post);
                $this->em->flush();

                return $this->json([
                    'message' => 'success',
                    'post' => $this->returnPost($post),
                ]);
            }
        } catch (\Throwable $th) {
            return $this->json(['message' => $th->getMessage()]);
        }
    }
}
