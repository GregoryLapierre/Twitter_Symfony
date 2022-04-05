<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/page{page}/search{search}/order{order}', name: 'app_index_search', methods:['GET'])]
    #[Route('/search', name: 'app_index_search_simple', methods:['POST'])]
    #[Route('/', name: 'app_index')]
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $manager): Response
    {
        // Récupération des valeurs reçues par URL ou formulaire de recherche
        $page = $request->get('page') ?? 1;
        $search = $request->get('search') ?? 0;
        $order = $request->get('order') ?? 'popular';
        
        if (empty($search)) {
            $search = 0; // Remplace une recherche avec un champs vide par l'entier 0
        }

        // Recherche des posts par page, titre, hashtag
        $posts = $postRepository->search($page, $search, $order);
        
        // Compte le nombre de pages total
        $pages = $postRepository->countPages($search);

        // Création du formulaire
        $post = new Post();
        $post->setStatus('opened');

        $form = $this->createFormBuilder($post)
                ->add('title', TextType::class,[
                'label'=>'Titre'
                ])
                ->add('content', TextareaType::class,[
                'label'=>'Message',
                'attr' =>['rows'=>5]
                ]);

        $form = $form->getForm();
        $form->handleRequest($request);

        // Traitement du formulaire
        if($this->getUser() && $form->isSubmitted() && $form->isValid())
        {
            $user = $this->getUser();

            $post->setCreatedAt(new \DateTimeImmutable());
            $post->setUser($user);

            $manager->persist($post);
            $manager->flush();

            return $this->redirectToRoute('app_index');
        }
        

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'posts'=> $posts,
            'formPost'=> $form->createView(),
            'pages' => $pages,
            'currentPage' => $page,
            'search'=> $search,
            'order' => $order
        ]);
    }
}
