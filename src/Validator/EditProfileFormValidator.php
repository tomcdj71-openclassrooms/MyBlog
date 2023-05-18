<?php

declare(strict_types=1);

namespace App\Validator;

use App\Helper\SecurityHelper;
use App\Manager\UserManager;
use App\Router\Session;
use App\Service\CsrfTokenService;

class EditProfileFormValidator extends BaseValidator
{
    protected Session $session;
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;
    protected SecurityHelper $securityHelper;

    public function __construct(UserManager $userManager, Session $session, CsrfTokenService $csrfTokenService, SecurityHelper $securityHelper)
    {
        parent::__construct($userManager, $session, $csrfTokenService);
        $this->securityHelper = $securityHelper;
    }

    public function validate(array $data): array
    {
        $validationRules = [
            'firstName' => [
                'constraints' => [
                    'required' => true,
                    'length' => [
                        'min' => 2, 'minErrorMsg' => 'Le prénom doit contenir plus de 2 caractères.',
                        'max' => 60, 'maxErrorMsg' => 'Le prénom ne doit pas dépasser 60 caractères.',
                    ],
                ],
            ],
            'lastName' => [
                'constraints' => [
                    'required' => true,
                    'length' => [
                        'min' => 2, 'minErrorMsg' => 'Le nom de famille doit contenir plus de 2 caractères.',
                        'max' => 60, 'maxErrorMsg' => 'Le nom de famille ne doit pas dépasser 60 caractères.',
                    ],
                ],
            ],
            'bio' => [
                'constraints' => [
                    'required' => false,
                    'length' => [
                        'min' => 10, 'minErrorMsg' => 'La biographie doit contenir au moins 10 caractères.',
                        'max' => 500, 'maxErrorMsg' => 'La biographie ne doit pas dépasser 500 caractères.',
                    ],
                ],
            ],
            'csrfToken' => [
                'constraints' => [
                    'required' => true,
                    'type' => 'csrf',
                    'csrfKey' => 'editProfile',
                ],
            ],
            'github' => [
                'constraints' => [
                    'required' => false,
                    'type' => 'username',
                ],
            ],
            'linkedin' => [
                'constraints' => [
                    'required' => false,
                    'type' => 'username',
                ],
            ],
            'twitter' => [
                'constraints' => [
                    'required' => false,
                    'type' => 'username',
                ],
            ],
            'facebook' => [
                'constraints' => [
                    'required' => false,
                    'type' => 'username',
                ],
            ],
            'avatar' => [
                'constraints' => [
                    'required' => false,
                    'type' => 'file',
                    'fileType' => ['value' => ['jpg', 'jpeg', 'png'], 'errorMsg' => 'L\'image doit être au format jpg, jpeg ou png.'],
                    'fileSize' => ['value' => 1000000, 'errorMsg' => 'L\'image ne doit pas dépasser 1Mo.'],
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }
}
