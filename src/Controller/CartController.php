<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Enum\OrderStatus;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'cart_show')]
    public function showCart(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $total = 0;
        $cartWithProducts = [];
        
        foreach ($cart as $productId => $details) {
            $product = $productRepository->find($productId);
            $tax = $product->getTax();

            if ($product) {
                $cartWithProducts[] = [
                    'product' => $product,
                    'quantity' => $details['quantity'],
                ];
            }
        }
        
        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'cart' => $cartWithProducts,
            'total' => $total,
            'tax' => $tax
        ]);
    }

    #[Route('/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function addToCart(
        Product $product,Request $request, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        $productId = $product->getId();
        $quantity = $request->request->get('quantity', 1);

        if (!isset($cart[$productId])) {
            $cart[$productId] = ['quantity' => $quantity];
        } else {
            $cart[$productId]['quantity'] += $quantity;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/remove/{id}', name: 'cart_remove')]
    public function removeFromCart(Product $product, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        unset($cart[$product->getId()]);

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/checkout', name: 'cart_checkout')]
    public function checkout(SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $session->get('cart', []);

        if (empty($cart)) {
            return $this->redirectToRoute('cart_show');
        }

        $order = new Order();
        $order->setUser($this->getUser());
        $order->setCreatedAt(new \DateTime());
        $order->setStatus(OrderStatus::PENDING);

        foreach ($cart as $productId => $details) {
            $product = $productRepository->find($productId);
            if ($product) {
                $orderItem = new OrderItem();
                $orderItem->setProduct($product);
                $orderItem->setQuantity($details['quantity']);
                $orderItem->setOrderReference($order);

                $entityManager->persist($orderItem);
            }
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $session->remove('cart');

        return $this->redirectToRoute('order_summary', ['id' => $order->getId()]);
    }
}
