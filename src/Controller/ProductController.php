<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response
    {
        $order = $request->query->get('order', 'asc'); 
        $searchTerm = $request->query->get('name', '');
        $categoryId = $request->query->get('category_id');
        $categories = $categoryRepository->findAll();
    
        $page = $request->query->getInt('page', 1);
        $limit = 10;
    
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'asc';
        }
    
        if (!empty($searchTerm)) {
            $queryBuilder = $productRepository->findBySearchAndFilter($searchTerm, $order);
        } elseif ($categoryId) {
            $queryBuilder = $productRepository->findBy(['category' => $categoryId], ['price' => $order]);
        } else {
            $queryBuilder = $productRepository->filterByPrice($order);
        }

        if ($categoryId) {
            $queryBuilder = $productRepository->findByCategory($categoryId, $order);
        } else {
            $queryBuilder = $productRepository->filterByPrice($order);
        }        
    
        $paginator = new Pagerfanta(new QueryAdapter($queryBuilder));
        $paginator->setCurrentPage($page);
        $paginator->setMaxPerPage($limit);
    
        return $this->render('product/index.html.twig', [
            'controller_name'   => 'ProductController',
            'page_name'         => !empty($searchTerm) ? 'Search Results' : 'Product',
            'products'          => $paginator,
            'order'             => $order,
            'search_term'       => $searchTerm,
            'categories'        => $categories,
            'selected_category' => $categoryId,
            'currentPage'       => $paginator->getCurrentPage(),
            'totalPages'        => $paginator->getNbPages(),
            'nextPage'          => $paginator->hasNextPage() ? $paginator->getNextPage() : null,
            'previousPage'      => $paginator->hasPreviousPage() ? $paginator->getPreviousPage() : null,
            'selected_category' => $categoryId
        ]);
    }

    #[Route('/search', name: 'product_search')]
    public function searchByName(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response
    {
        $name = $request->query->get('name', '');
        
        $products = $productRepository->findByName($name);
        $categories = $categoryRepository->findAll();

        return $this->render('product/index.html.twig', [
            'controller_name'   => 'ProductController',
            'page_name'         => 'Search Results',
            'products'          => $products,
            'order'             => 'asc',
            'search_term'       => $name,
            'categories'        => $categories,
            'selected_category' => null
        ]);
    }

    #[Route('/category', name: 'product_category')]
    public function filterByCategory(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        Request $request
    ): Response
    {
        $categories = $categoryRepository->findAll();
        $categoryId = $request->query->get('category_id');

        if ($categoryId) {
            $products = $productRepository->findBy(['category' => $categoryId]);
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('product/index.html.twig', [
            'controller_name'   => 'ProductController',
            'page_name'         => 'Product by Category',
            'products'          => $products,
            'order'             => 'asc',
            'search_term'       => null,
            'categories'        => $categories,
            'selected_category' => $categoryId 
        ]);
    }

    #[Route('/detail/{id}', name:'product_item')]
    public function productItem(ProductRepository $productRepository, int $id): Response
    {
        $product = $productRepository->find($id);

        return $this->render('product/product-item.html.twig', [
            'controller_name' => 'ProductController',
            'page_name'       => 'Product',
            'product'         => $product
        ]);
    }
}
