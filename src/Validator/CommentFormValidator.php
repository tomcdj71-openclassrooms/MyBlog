<?php

namespace App\Validator;

use App\Manager\UserManager;
use App\Router\Session;
use App\Service\CsrfTokenService;

class CommentFormValidator extends BaseValidator
{
    protected Session $session;
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;

    public function __construct(UserManager $userManager, Session $session, CsrfTokenService $csrfTokenService)
    {
        parent::__construct($userManager, $session, $csrfTokenService);
        $this->csrfTokenService = $csrfTokenService;
    }

    public function validate($data)
    {
        $validationRules = [
            'content' => [
                'constraints' => [
                    'required' => true, 'errorMsg' => 'Veuillez renseigner votre commentaire.',
                    'length' => [
                        'min' => 10, 'minErrorMsg' => 'Votre commentaire doit contenir au moins 10 caractères.',
                        'max' => 500, 'maxErrorMsg' => 'Votre commentaire doit contenir 500 caractères maximum.'],
                ],
            ],
            'csrfToken' => [
                'constraints' => [
                    'required' => true,
                    'type' => 'csrf',
                    'csrfKey' => 'comment',
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }
}
