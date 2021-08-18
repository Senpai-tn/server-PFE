<?php

namespace App\Controller;

use App\Entity\Post;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function index(): Response
    {
        $posts = $this->em->getRepository(Post::class)->findAll();
        $list = [];
        foreach ($posts as $key => $post) {
            $list[$key]['title'] = $post->getTitle();
            $list[$key]['description'] = $post->getDescription();
            $list[$key]['created_at'] = $post->getCreatedAt();
            $list[$key]['deleted_at'] = $post->getDeletedAt();
        }
        return $this->json($list);
    }

    /**
     * @Route("/", name="add_post",methods={"POST"})
     */
    public function FunctionName(): Response
    {
        $post = new Post();
        $post->setTitle('title1');
        $post->setDescription('Description1' . new DateTime());
        $post->setCreatedAt(new DateTimeImmutable());
        $post->setDeletedAt(null);
        $this->em->persist($post);
        $this->em->flush();
        return new Response('add' . $post->getId());
    }
}
