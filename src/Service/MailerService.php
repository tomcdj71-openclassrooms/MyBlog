<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $from, string $to, string $subject, string $body): void
    {
        try {
            $email = (new Email())
                ->from($from)
                ->to($to)
                ->subject($subject)
                ->text($body)
            ;
            $this->mailer->send($email);
        } catch (\Exception $error) {
            throw new \Exception('Une erreur est survenue lors de l\'envoi du mail.');
        }
    }
}
