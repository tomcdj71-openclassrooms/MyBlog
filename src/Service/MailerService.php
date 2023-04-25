<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private CustomSmtpTransport $transport;
    private MailerInterface $mailer;

    public function __construct(CustomSmtpTransport $transport, MailerInterface $mailer)
    {
        $this->transport = $transport;
        $this->mailer = $mailer;
    }

    public function send(string $fromEmail, string $toEmail, string $subject, string $body): void
    {
        $email = (new Email())
            ->from($fromEmail)
            ->to($toEmail)
            ->subject($subject)
            ->html($body)
        ;

        try {
            $this->mailer->send($email);
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            throw new \RuntimeException('Unable to send email: '.$e->getMessage());
        }
    }
}
