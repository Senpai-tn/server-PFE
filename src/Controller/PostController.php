<?php

namespace App\Controller;

use App\Entity\Post;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @Route("/", name="posts" , methods={"GET"})
     */
    public function index(Request $r): Response
    {
        try {
            $data = json_decode($r->getContent(), true);

            if (!isset($data['id'])) {
                $posts = $this->em->getRepository(Post::class)->findAll();
                $list = [];
                foreach ($posts as $key => $post) {
                    $list[$key]['id'] = $post->getId();
                    $list[$key]['title'] = $post->getTitle();
                    $list[$key]['description'] = $post->getDescription();
                    $list[$key]['created_at'] = $post->getCreatedAt();
                    $list[$key]['deleted_at'] = $post->getDeletedAt();
                }
                return $this->json($list);
            } else {
                return new Response('object');
            }
        } catch (\Throwable $th) {
            return $this->json(['message' => $th->getMessage()]);
        }
    }

    /**
     * @Route("/", name="add_post",methods={"POST"})
     */
    public function FunctionName(Request $r): Response
    {
        try {
            $post = new Post();
            $post->setTitle('title');
            $post->setDescription('Description1');
            $post->setCreatedAt(new DateTimeImmutable());
            $post->setDeletedAt(null);
            $data = json_decode($r->getContent(), true);
            $files = [];
            $i = 1;
            while ($r->files->get('file' . $i) != null) {
                $files[$i] = $r->files->get('file' . $i);
                $i++;
            }

            foreach ($files as $file) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move(
                    $this->getParameter('images_directory') .
                        '/' .
                        date('Y-m-d'),
                    $filename
                );
            }

            dd($files);
            die();

            /*$this->em->persist($post);
            $this->em->flush();
            $list = [];
            $list['id'] = $post->getId();
            $list['title'] = $post->getTitle();
            $list['description'] = $post->getDescription();
            $list['created_at'] = $post->getCreatedAt();
            $list['deleted_at'] = $post->getDeletedAt();*/
            return $this->json(['message' => 'success', 'post' => 0]);
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
                $list = [];
                $list['id'] = $post->getId();
                $list['title'] = $post->getTitle();
                $list['description'] = $post->getDescription();
                $list['created_at'] = $post->getCreatedAt();
                $list['deleted_at'] = $post->getDeletedAt();
                return $this->json(['message' => 'success', 'post' => $list]);
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
                $this->em->remove($post);
                $this->em->flush();
                $list = [];
                $list['id'] = $post->getId();
                $list['title'] = $post->getTitle();
                $list['description'] = $post->getDescription();
                $list['created_at'] = $post->getCreatedAt();
                $list['deleted_at'] = $post->getDeletedAt();
                return $this->json(['message' => 'success', 'post' => $list]);
            }
        } catch (\Throwable $th) {
            return $this->json(['message' => $th->getMessage()]);
        }
    }
}
