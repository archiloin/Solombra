<?php

namespace App\Controller;

use App\Entity\Forum\Forum;
use App\Entity\Forum\Message;
use App\Entity\Forum\Post;
use App\Entity\Forum\Topic;
use App\Entity\User;
use App\Entity\Forum\Report;
use App\Form\Forum\MessageType;
use App\Form\Forum\PostType;
use App\Form\Forum\ReportType;
use App\Form\Forum\TopicType;
use App\Repository\Forum\MessageRepository;
use App\Repository\Forum\PostRepository;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route(path: '/member/forum')]
class ForumController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_forum')]
    public function index(): Response
    {
        $forum = $this->entityManager->getRepository(Forum::class)->find(1);
        $topics = $this->entityManager->getRepository(Topic::class)->findAll();

        return $this->render('forum/index.html.twig', [
            'forum' => $forum,
            'topics' => $topics,
        ]);
    }

    #[Route('/topic/{id}/{page}', name: 'app_forum_topic', defaults: ['page' => 1])]
    public function topic(int $id, $page, Request $request, PaginatorInterface $paginator, PostRepository $postRepository): Response
    {
        $topic = $this->entityManager->getRepository(Topic::class)->find($id);

        $posts = $this->entityManager->getRepository(Post::class)->findBy(
            ['topic' => $id],
            ['createdAt' => 'DESC']
        );        

        if (!$topic) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        // Récupérer les messages associés
        $query = $postRepository->createQueryBuilder('p')
            ->where('p.topic = :topic')
            ->setParameter('topic', $topic)
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $page, // Récupération via l'URL au lieu de $request->query->getInt()
            10
        );

        return $this->render('forum/topic/index.html.twig', [
            'topic' => $topic,
            'posts' => $posts,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/{id}/topic/new', name: 'app_forum_topic_new')]
    public function newTopic(Request $request, $id): Response
    {
        $topic = new Topic();
        $forum = $this->entityManager->getRepository(Forum::class)->find($id);

        $form = $this->createForm(TopicType::class, $topic);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $topic->setForum($forum);
            $this->entityManager->persist($topic);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Nouveau sujet créée avec succès.');
            return $this->redirectToRoute('app_forum');
        }
    
        return $this->render('forum/topic/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }    

    #[Route('/post/view/{id}/{page}', name: 'app_forum_post', defaults: ['page' => 1])]
    public function post(int $id, $page, Request $request, PaginatorInterface $paginator, MessageRepository $messageRepository): Response
    {
        $post = $this->entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Sujet non trouvé');
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message->setPost($post);
            $message->setPublishAt(new \DateTimeImmutable());
            $message->setAuthor($this->getUser());
            $post->setReplyCount($post->getReplyCount() + 1); // Mise à jour du nombre de réponses
            $this->entityManager->persist($message);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Votre réponse a été ajoutée avec succès.');
            return $this->redirectToRoute('app_forum_post', ['id' => $id]);
        }

        // Récupérer les messages associés
        $query = $messageRepository->createQueryBuilder('m')
            ->where('m.post = :post')
            ->setParameter('post', $post)
            ->orderBy('m.publishAt', 'ASC')
            ->getQuery();

        $pagination = $paginator->paginate(
            $query,
            $page, // Récupération via l'URL au lieu de $request->query->getInt()
            10
        );

        $user = $this->getUser();

        if ($user && !$post->hasBeenViewedBy($user)) {
            $post->addViewedByUser($user);
            $this->entityManager->persist($post);
            $this->entityManager->flush();
        }

        return $this->render('forum/post/index.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'messages' => $post->getMessages(),
            'pagination' => $pagination,
        ]);
    }

    #[Route('/post/new/{topicId}', name: 'app_forum_post_new')]
    public function newPost(int $topicId, Request $request): Response
    {
        $topic = $this->entityManager->getRepository(Topic::class)->find($topicId);
    
        if (!$topic) {
            throw $this->createNotFoundException('Sujet non trouvée');
        }
    
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setTopic($topic);
            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setAuthor($this->getUser());
    
            $this->entityManager->persist($post);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Nouveau sujet créé avec succès.');
            return $this->redirectToRoute('app_forum_topic', ['id' => $topicId]);
        }
    
        return $this->render('forum/post/new.html.twig', [
            'form' => $form->createView(),
            'topic' => $topic,
        ]);
    }

    #[Route('/post/edit/{id}', name: 'app_forum_post_edit')]
    public function editPost(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé.');
        }

        // Vérifie si l'utilisateur est l'auteur du post
        if ($post->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas la permission d'éditer ce post.");
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUpdatedAt(new \DateTimeImmutable());
            $post->setEdited(true);
            $entityManager->flush();

            $this->addFlash('success', 'Le sujet a été mis à jour avec succès.');

            return $this->redirectToRoute('app_forum_post', ['id' => $post->getId()]);
        }

        return $this->render('forum/post/edit.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }

    #[Route('/message/new/{postId}', name: 'app_forum_message_new', methods: ['POST'])]
    public function newMessage(int $postId, Request $request): Response
    {
        $post = $this->entityManager->getRepository(Post::class)->find($postId);
    
        if (!$post) {
            throw $this->createNotFoundException('Sujet non trouvé');
        }
    
        $form = $this->createForm(MessageType::class, $message);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = new Message();
            $message->setPost($post);
            $message->setPublishAt(new \DateTimeImmutable());
            $message->setAuthor($this->getUser());
            $post->setReplyCount($post->getReplyCount() + 1); // Mise à jour du nombre de réponses
            $this->entityManager->persist($message);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Votre réponse a été ajoutée avec succès.');
            return $this->redirectToRoute('app_forum_post', ['id' => $postId]);
        }
    
        return $this->redirectToRoute('app_forum_post', ['id' => $postId]);
    }

    #[Route('/message/edit/{id}', name: 'app_forum_message_edit')]
    public function editMessage(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $message = $entityManager->getRepository(Message::class)->find($id);

        if (!$message) {
            throw $this->createNotFoundException('Post non trouvé.');
        }

        // Vérifie si l'utilisateur est l'auteur du post
        if ($message->getAuthor() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'avez pas la permission d'éditer ce post.");
        }

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setUpdatedAt(new \DateTimeImmutable());
            $message->setEdited(true);
            $entityManager->flush();

            $this->addFlash('success', 'Le sujet a été mis à jour avec succès.');

            return $this->redirectToRoute('app_forum_post', ['id' => $message->getPost()->getId()]);
        }

        return $this->render('forum/message/edit.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    #[Route('/user/{id}', name: 'app_forum_user')]
    public function user(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        return $this->render('forum/user.html.twig', [
            'user' => $user,
            'messages' => $user->getMessages(),
        ]);
    }

    #[Route('/report', name: 'app_forum_report')]
    public function viewReport(): Response
    {
        $reports = $this->entityManager->getRepository(Report::class)->findAll();

        return $this->render('forum/report.html.twig', [
            'reports' => $reports,
        ]);
    }

    #[Route('/report/{postId}/{messageId}', name: 'app_forum_report', methods: ['GET', 'POST'])]
    public function report(int $postId, int $messageId, Request $request): Response
    {
        $post = $this->entityManager->getRepository(Post::class)->find($postId);
        $message = $this->entityManager->getRepository(Message::class)->find($messageId);
    
        if (!$post) {
            throw $this->createNotFoundException('Sujet non trouvé');
        }
    
        $report = new Report();
        $form = $this->createForm(ReportType::class, $report);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $report->setPost($post);
            $report->setMessage($message);
            $report->setCreatedAt(new \DateTimeImmutable());
            $report->setUser($this->getUser());
            $this->entityManager->persist($report);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Votre réponse a été ajoutée avec succès.');
            return $this->redirectToRoute('app_forum_post', ['id' => $postId]);
        }
        return $this->render('forum/message/report.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'message' => $message,
        ]);
    }
}
