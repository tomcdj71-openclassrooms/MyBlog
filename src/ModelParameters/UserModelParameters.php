<?php

declare(strict_types=1);

namespace App\ModelParameters;

class UserModelParameters
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $createdAt;
    public $updatedAt;
    public $isEnabled;
    public $avatar;
    public $bio;
    public $firstName;
    public $lastName;
    public $twitter;
    public $facebook;
    public $linkedin;
    public $github;

    public static function createFromData(array $data): self
    {
        $userModelParams = new self();
        $userModelParams->id = (int) ($data['user_id'] ?? $data['id']);
        $userModelParams->username = $data['username'] ?? null;
        $userModelParams->email = $data['email'] ?? null;
        $userModelParams->password = $data['password'] ?? null;
        $userModelParams->createdAt = $data['created_at'] ?? null;
        $userModelParams->role = $data['role'] ?? null;
        $userModelParams->avatar = $data['avatar'] ?? null;
        $userModelParams->bio = $data['bio'] ?? null;
        $userModelParams->firstName = $data['firstName'] ?? null;
        $userModelParams->lastName = $data['lastName'] ?? null;
        $userModelParams->twitter = $data['twitter'] ?? null;
        $userModelParams->facebook = $data['facebook'] ?? null;
        $userModelParams->linkedin = $data['linkedin'] ?? null;
        $userModelParams->github = $data['github'] ?? null;

        return $userModelParams;
    }
}
