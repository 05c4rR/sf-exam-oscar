<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Tax;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/order')]
class OrderController extends AbstractController
{
    #[Route('/summary/{id}', name: 'order_summary')]
    public function orderSummary(int $id, EntityManagerInterface $entityManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->find($id);
        $taxVat = $entityManager->getRepository(Tax::class)->findOneBy(['name' => 'T.V.A']);
        $tax = $taxVat->getValue();

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('La commande n\'existe pas ou erreur dans l\'authentification de l\'utilisateur');
        }

        return $this->render('order/summary.html.twig', [
            'order' => $order,
            'tax' => $tax
        ]);
    }

    #[Route('/pay/{id}', name: 'stripe_payment', methods: ['POST'])]
    public function payWithStripe(int $id, EntityManagerInterface $entityManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->find($id);
        $taxVat = $entityManager->getRepository(Tax::class)->findOneBy(['name' => 'T.V.A']);
        $tax = $taxVat->getValue();

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable ou accès refusé.');
        }

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $totalAmount = 0;
        foreach ($order->getOrderItems() as $item) {
            $totalAmount += $item->getPrice() * $item->getQuantity();
        }

        $totalWithTax = $totalAmount * $tax;
        $totalIntegerForStripe = (int)round($totalWithTax * 100);
        
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Commande #' . $order->getId(),
                    ],
                    'unit_amount' => $totalIntegerForStripe,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',

            'success_url' => $this->generateUrl('payment_success', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            
        ]);
        // Décommenter la ligne ci dessous pour accéder au portail de paiement de Stripe depuis cette url
        // dd($session->url);
        return $this->redirect($session->url);
    }

    #[Route('/payment/success/{id}', name: 'payment_success')]
    public function paymentSuccess(int $id, EntityManagerInterface $entityManager): Response
    {
        $order = $entityManager->getRepository(Order::class)->find($id);
        $order->setStatus(OrderStatus::CONFIRMED);

        $entityManager->persist($order);
        $entityManager->flush();
        
        return $this->render('order/success.html.twig');
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
        return $this->render('order/cancel.html.twig');
    }

}
