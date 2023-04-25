<?php

declare(strict_types=1);

namespace App\Service;

use App\Config\Configuration;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class CustomSmtpTransport extends SmtpTransport
{
    private string $username;
    private string $password;
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;

        $mailerConfig = $this->configuration->get('mailer');

        $socketStream = new SocketStream();
        $socketStream->setHost($mailerConfig['smtp_host']);
        $socketStream->setPort($mailerConfig['smtp_port']);

        if ($mailerConfig['smtp_encryption']) {
            $socketStream->setStreamOptions([
                'ssl' => [
                    'crypto_method' => STREAM_CRYPTO_METHOD_TLS_CLIENT
                        | STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                        | STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT,
                ],
            ]);
        } else {
            $socketStream->disableTls();
        }

        parent::__construct($socketStream);

        $this->username = $mailerConfig['smtp_user'];
        $this->password = $mailerConfig['smtp_password'];
    }

    protected function getStreamOptions(): array
    {
        $options = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        if (null !== $this->configuration->get('mailer.smtp_auth_mode')) {
            $options['auth'] = $this->configuration->get('mailer.smtp_auth_mode');
            $options['username'] = $this->username;
            $options['password'] = $this->password;
        }

        return $options;
    }
}
