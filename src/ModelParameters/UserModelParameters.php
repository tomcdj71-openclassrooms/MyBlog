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
    public $rememberMeToken;
    public $rememberMeExpiresAt;
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
        $userModelParams->username = $data['username'];
        $userModelParams->email = $data['email'];
        $userModelParams->password = $data['password'];
        $userModelParams->createdAt = $data['created_at'];
        $userModelParams->role = $data['role'];
        $userModelParams->avatar = $data['avatar'] ?? null;
        $userModelParams->bio = $data['bio'];
        $userModelParams->rememberMeToken = $data['remember_me_token'];
        $userModelParams->rememberMeExpiresAt = $data['remember_me_expires_at'];
        $userModelParams->firstName = $data['firstName'];
        $userModelParams->lastName = $data['lastName'];
        $userModelParams->twitter = $data['twitter'];
        $userModelParams->facebook = $data['facebook'];
        $userModelParams->linkedin = $data['linkedin'];
        $userModelParams->github = $data['github'];

        return $userModelParams;
    }
}
