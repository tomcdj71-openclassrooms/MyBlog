<?php

declare(strict_types=1);

namespace App\Validator;

use App\Manager\UserManager;
use App\Router\Session;
use App\Service\CsrfTokenService;

class PostFormValidator extends BaseValidator
{
    protected Session $session;
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;

    public function __construct(UserManager $userManager, Session $session, CsrfTokenService $csrfTokenService)
    {
        parent::__construct($userManager, $session, $csrfTokenService);
    }

    public function validate(array $data): array
    {
        $validationRules = [
            'title' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'Le titre est obligatoire.',
                    'length' => [
                        'min' => 3, 'minErrorMsg' => 'Le titre doit contenir plus de 3 caractères.',
                        'max' => 60, 'maxErrorMsg' => 'Le titre ne doit pas dépasser 60 caractères.',
                    ],
                ],
            ],
            'chapo' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'Le chapo est obligatoire.',
                    'length' => [
                        'min' => 3, 'minErrorMsg' => 'Le chapo doit contenir plus de 3 caractères.',
                        'max' => 255, 'maxErrorMsg' => 'Le chapo ne doit pas dépasser 255 caractères.',
                    ],
                ],
            ],
            'content' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'Le contenu est obligatoire.',
                    'length' => [
                        'min' => 3, 'minErrorMsg' => 'Le contenu doit contenir plus de 3 caractères.',
                        'max' => 10000, 'maxErrorMsg' => 'Le contenu ne doit pas dépasser 10000 caractères.',
                    ],
                ],
            ],
            'category' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'La catégorie est obligatoire.',
                    'type' => 'int', 'errorMsg' => 'La catégorie doit être un nombre entier.',
                ],
            ],
            'tags' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'Veuillez selectionner au moins un tag.',
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }
}
