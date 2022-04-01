<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(PostRepository $postRepository, Request $request): Response
    {   
        $search = $request->get('search');
        $post = $postRepository->search($search);

        return $this->render('search/index.html.twig', [
            'posts' => $post
        ]);
    }
}
