<?php

declare(strict_types=1);

namespace App\Validator;

class ContactFormValidator extends BaseValidator
{
    public function validate(array $data): array
    {
        $validationRules = [
            'name' => [
                'constraints' => [
                    'required' => true,
                    'length' => [
                        'min' => 2, 'minErrorMsg' => 'Le nom doit contenir plus de 2 caractères.',
                        'max' => 100, 'maxErrorMsg' => 'Le nom ne doit pas dépasser 100 caractères.',
                    ],
                ],
            ],
            'email' => [
                'constraints' => [
                    'required' => true,
                    'email' => true,
                ],
            ],
            'subject' => [
                'constraints' => [
                    'required' => true,
                    'length' => [
                        'min' => 8, 'minErrorMsg' => 'Le sujet doit contenir plus de 8 caractères.',
                        'max' => 150, 'maxErrorMsg' => 'Le sujet ne doit pas dépasser 150 caractères.',
                    ],
                ],
            ],
            'message' => [
                'constraints' => [
                    'required' => true,
                    'length' => [
                        'min' => 8, 'minErrorMsg' => 'Le message doit contenir plus de 8 caractères.',
                        'max' => 800, 'maxErrorMsg' => 'Le message ne doit pas dépasser 800 caractères.',
                    ],
                ],
            ],
            'csrfToken' => [
                'constraints' => [
                    'required' => true,
                    'type' => 'csrf',
                    'csrfKey' => 'contact',
                ],
            ],
        ];

        return $this->validateData($data, $validationRules);
    }
}
