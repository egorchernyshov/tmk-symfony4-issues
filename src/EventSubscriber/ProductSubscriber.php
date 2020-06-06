<?php

namespace App\EventSubscriber;

use App\Entity\Product;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var TemplatedEmail */
    private $template;

    /**
     * ProductSubscriber constructor.
     *
     * @param MailerInterface $mailer
     * @param ParameterBagInterface $bag
     */
    public function __construct(MailerInterface $mailer, ParameterBagInterface $bag)
    {
        $this->mailer = $mailer;
        $this->bag = $bag;

        $this->template = (new TemplatedEmail())
            ->from($bag->get('app.mail.notify.from'))
            ->to($bag->get('app.mail.notify.to'));
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.event_listener' => [
                ['onUpdated'],
                ['onAdded'],
            ],
        ];
    }

    public function onUpdated(Product $product)
    {
        $this->send('Product was updated', $product);
    }

    public function onAdded(Product $product)
    {
        $this->send('Product was added', $product);
    }

    public function send(string $subject, Product $product)
    {
        $email = $this->template
            ->subject($subject)
            ->htmlTemplate('emails/product.html.twig')
            ->context([
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'price' => $product->getPrice(),
            ]);

        $this->mailer->send($email);
    }
}
