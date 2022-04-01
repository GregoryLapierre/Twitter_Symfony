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
    #[Route('/', name: 'app_index')]
    public function index(PostRepository $postRepository, Request $request, EntityManagerInterface $manager): Response
    {   $posts = $postRepository
            ->findSearch();
    
       
            $post = new Post();
            $post -> setStatus('Ouvert');
            $form = $this->createFormBuilder($post)
                         ->add('title', TextType::class,[
                            'label'=>'Titre'
                         ])
                         ->add('content', TextareaType::class,[
                            'label'=>'Message',
                            'attr' =>['rows'=>5]
                         ]);

                        if ($this->isGranted('ROLE_ADMIN')) {
                            $form->add('status', ChoiceType::class, [
                            'choices'  => [
                                'Ouvert' => 'Ouvert',
                                'Fermé' => 'Fermé',
                                'Modéré' => 'Modéré'       
                                ],
                            'placeholder'=>'Choisir un statut',
                            'label'=>'Statut'   
                            ]);
                        };
                        $form = $form->getForm();
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
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
            'formPost'=> $form->createView()
        ]);
    }

    



    // #[Route('/{id}', name: 'showPost')]
    // public function show(int $id, PostRepository $postRepository): Response
    // {
        

    //     // ...
    //     return $this->render('post/index.html.twig', [
    //         'controller_name' => 'PostController',
    //         'posts'=> $posts
    //     ]);
    // }
}
