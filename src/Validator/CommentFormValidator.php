<?php

namespace App\Validator;

class CommentFormValidator extends BaseValidator
{
    private $securityHelper;

    public function __construct($securityHelper)
    {
        $this->securityHelper = $securityHelper;
    }

    public function validate($data)
    {
        $errors = [];
        $valid = true;

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
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('login', $token) ? '' : $errorMsg;
    }
}
