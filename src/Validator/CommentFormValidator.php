<?php

namespace App\Validator;

use App\Helper\SecurityHelper;

class CommentFormValidator extends BaseValidator
{
    protected SecurityHelper $securityHelper;

    public function __construct(SecurityHelper $securityHelper)
    {
        $this->securityHelper = $securityHelper;
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
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('comment', $token) ? '' : $errorMsg;
    }
}
