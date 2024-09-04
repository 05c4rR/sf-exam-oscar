<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $order = $request->query->get('order', 'asc'); 
        $searchTerm = $request->query->get('name', '');

        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'asc';
        }
        if (!empty($searchTerm)) {
            $products = $productRepository->findBySearchAndFilter($searchTerm, $order);
        } else {
            $products = $productRepository->filterByPrice($order);
        }

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'page_name' => !empty($searchTerm) ? 'Search Results' : 'Product',
            'products' => $products,
            'order' => $order,
            'search_term' => $searchTerm
        ]);
    }

    #[Route('/search', name: 'product_search')]
    public function searchByName(ProductRepository $productRepository, Request $request): Response
    {
        $name = $request->query->get('name', '');

        $products = $productRepository->findByName($name);

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'page_name'       => 'Search Results',
            'products'        => $products,
            'order'           => 'asc',
            'search_term'     => $name
        ]);
    }

    #[Route('/detail/{id}', name:'product_item')]
    public function productItem(ProductRepository $productRepository, int $id): Response
    {
        $product = $productRepository->find($id);

        return $this->render('product/product-item.html.twig', [
            'controller_name' => 'ProductController',
            'page_name'       => 'Product',
            'product'        => $product
        ]);
    }
}
