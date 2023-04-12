<?php

namespace App\Validator;

class EditProfileFormValidator
{
    public function validate(array $data): array
    {
        $valid = true;
        $errors = [];

        $twitterRegex = '/^(https?:\/\/)?(www\.)?twitter\.com\/([a-zA-Z0-9_]{1,15})$/';
        $facebookRegex = '/^(https?:\/\/)?(www\.)?facebook\.com\/([a-zA-Z0-9_]{1,15})$/';
        $githubRegex = '/^(https?:\/\/)?(www\.)?github\.com\/([a-zA-Z0-9_]{1,15})$/';
        $linkedinRegex = '/^(https?:\/\/)?(www\.)?linkedin\.com\/([a-zA-Z0-9_]{1,15})$/';

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $valid = false;
            $errors['email'] = 'Please enter a valid email address!';
        }

        if (isset($data['firstName']) && strlen($data['firstName']) > 60) {
            $valid = false;
            $errors['firstName'] = 'The first name may not be greater than 60 characters.';
        }

        if (isset($data['lastName']) && strlen($data['lastName']) > 60) {
            $valid = false;
            $errors['lastName'] = 'The last name may not be greater than 60 characters.';
        }

        if (isset($data['bio']) && strlen($data['bio']) > 500) {
            $valid = false;
            $errors['bio'] = 'The bio may not be greater than 500 characters.';
        }

        if (isset($data['twitter']) && !empty($data['twitter'])) {
            if (preg_match($twitterRegex, $data['twitter'], $matches)) {
                $parts = explode('/', $matches[3]);
                $data['twitter'] = end($parts);
            }
        }

        if (isset($data['facebook']) && !empty($data['facebook'])) {
            if (preg_match($facebookRegex, $data['facebook'], $matches)) {
                $parts = explode('/', $matches[3]);
                $data['facebook'] = end($parts);
            }
        }

        if (isset($data['github']) && !empty($data['github'])) {
            if (preg_match($githubRegex, $data['github'], $matches)) {
                $parts = explode('/', $matches[3]);
                $data['github'] = end($parts);
            }
        }

        if (isset($data['linkedin']) && !empty($data['linkedin'])) {
            if (preg_match($linkedinRegex, $data['linkedin'], $matches)) {
                $parts = explode('/', $matches[3]);
                $data['linkedin'] = end($parts);
            }
        }

        if (isset($_FILES['avatar']) && UPLOAD_ERR_OK === $_FILES['avatar']['error']) {
            $postData['avatar'] = $_FILES['avatar'];
        }

        return [
            'valid' => $valid,
            'errors' => $errors,
            'data' => $data,
        ];
    }
}
