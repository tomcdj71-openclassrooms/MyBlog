<?php

declare(strict_types=1);

namespace App\Validator;

use App\Helper\SecurityHelper;

class EditProfileFormValidator extends BaseValidator
{
    protected SecurityHelper $securityHelper;

    public function __construct(SecurityHelper $securityHelper)
    {
        $this->securityHelper = $securityHelper;
    }

    public function validate(array $data): array
    {
        $fields = ['email', 'firstName', 'lastName', 'bio'];
        foreach ($fields as $field) {
            $getter = 'get'.ucfirst($field);
            if (method_exists($this->securityHelper->getUser(), $getter)) {
                if (empty($data[$field]) || $data[$field] === $this->securityHelper->getUser()->{$getter}()) {
                    unset($data[$field]);
                }
            }
        }

        $validationRules = [
            'email' => [
                'constraints' => [
                    'required' => false, 'errorMsg' => 'Veuillez inscrire une adresse email valide!',
                    'unique' => true, 'errorMsg' => 'Cette adresse e-mail est déjà enregistrée.',
                    'type' => 'email',
                ],
            ],
            'firstName' => [
                'constraints' => [
                    'required' => false,
                    'length' => [
                        'min' => 2, 'minErrorMsg' => 'Le prénom doit contenir plus de 2 caractères.',
                        'max' => 60, 'maxErrorMsg' => 'Le prénom ne doit pas dépasser 60 caractères.',
                    ],
                ],
            ],
            'lastName' => [
                'constraints' => [
                    'required' => false,
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
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return $this->securityHelper->checkCsrfToken('editProfile', $token) ? '' : $errorMsg;
    }
}
