<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private MailerInterface $mailer;

    private string $mode;

    public function __construct(MailerInterface $mailer, string $mode)
    {
        $this->mailer = $mailer;
        $this->mode = $mode;
    }

    public function sendEmail(string $from, string $to, string $subject, string $body): ?string
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
            if ('dev' === $this->mode) {
                throw new \Exception("Un problème avec votre mailer DSN est survenu. Veuillez consulter la documentation pour plus d'informations.");
            }

            return 'Une erreur est survenue lors de l\'envoi du mail.';
        }

        return null;
    }
}
