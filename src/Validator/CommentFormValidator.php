<?php

namespace App\Validator;

class CommentFormValidator
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
            'content' => ['type' => 'empty', 'errorMsg' => 'Veuillez entrer un commentaire.', 'required' => true],
            'csrfToken' => ['type' => 'csrf', 'errorMsg' => 'Jeton CSRF invalide.', 'required' => true],
        ];

        foreach ($validationRules as $field => $rule) {
            if (isset($data[$field])) {
                switch ($rule['type']) {
                    case 'empty':
                        if (empty($data[$field])) {
                            $valid = false;
                            $errors[$field] = $rule['errorMsg'];
                        }

                        break;
                }
            }
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'data' => $data,
        ];
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return [
            'valid' => $this->securityHelper->checkCsrfToken('comment', $token),
            'errorMsg' => $errorMsg,
        ];
    }
}
