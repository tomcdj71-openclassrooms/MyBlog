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
        $validationRules = [
            'csrf_token' => ['type' => 'csrf', 'errorMsg' => 'Invalid CSRF token.', 'required' => true],
            'content' => ['type' => 'empty', 'errorMsg' => 'Please enter a comment.', 'required' => true],
        ];

        return $this->validateData($data, $validationRules);
    }

    protected function validateCsrfToken($token, $errorMsg)
    {
        return [
            'valid' => $this->securityHelper->checkCsrfToken('comment', $token),
            'errorMsg' => $errorMsg,
        ];
    }
}
