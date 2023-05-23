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
                return "Un problème avec votre mailer DSN est survenu. Veuillez consulter la documentation pour plus d'informations. Raison :".$error->getMessage();
            }

            return "Un problème survenu lors de l'envoi du mail.";
        }

        return null;
    }
}
