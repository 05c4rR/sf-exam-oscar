<?php

namespace App\EmailService;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class OrderEmailConfirmation
{
    public function __construct(
        private MailerInterface $mailer
    )
    {   
    }

    public function sendOrderConfirmationEmail($user, $order)
    {   
        
        $email = new TemplatedEmail();
        $email
            ->from('admin@test.com')
            ->to($user->getEmail())
            ->subject('Confirmation de votre commande # ' . $order->getId())
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context([
                'user' => $user,
                'order' => $order,
                'totalPrice' => $order->getTotalPrice(),
            ]);
        
            $this->mailer->send($email);
    }

}