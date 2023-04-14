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

        if (!isset($data['csrf_token'])) {
            $valid = false;
            $errors['csrf_token'] = 'CSRF token missing.';
        } elseif (!$this->securityHelper->checkCsrfToken('comment', $data['csrf_token'])) {
            $valid = false;
            $errors['csrf_token'] = 'Invalid CSRF token.';
        }

        if (!isset($data['content']) || empty($data['content'])) {
            $valid = false;
            $errors['content'] = 'Please enter a comment.';
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
        ];
    }
}
