<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {   
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'page_name'       => 'Accueil',
        ]);
    }

    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('index/about.html.twig', [
            'controller_name' => 'IndexController',
            'page_name'       => 'About',
        ]);
    }
}
