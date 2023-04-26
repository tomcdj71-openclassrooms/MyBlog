<?php

declare(strict_types=1);

namespace App\Model;

use App\ModelParameters\UserModelParameters;

class UserModel
{
    private $id;
    private $username;
    private $email;
    private $password;
    private $created_at;
    private $role;
    private $avatar;
    private $bio;
    private $remember_me_token;
    private $remember_me_expires_at;
    private $firstName;
    private $lastName;
    private $twitter;
    private $facebook;
    private $linkedin;
    private $github;

    public function __construct(UserModelParameters $userModelParams)
    {
        $this->id = $userModelParams->id;
        $this->username = $userModelParams->username;
        $this->email = $userModelParams->email;
        $this->password = $userModelParams->password;
        $this->created_at = $userModelParams->createdAt;
        $this->role = $userModelParams->role;
        $this->avatar = $userModelParams->avatar;
        $this->bio = $userModelParams->bio;
        $this->remember_me_token = $userModelParams->rememberMeToken;
        $this->remember_me_expires_at = $userModelParams->rememberMeExpiresAt;
        $this->firstName = $userModelParams->firstName;
        $this->lastName = $userModelParams->lastName;
        $this->twitter = $userModelParams->twitter;
        $this->facebook = $userModelParams->facebook;
        $this->linkedin = $userModelParams->linkedin;
        $this->github = $userModelParams->github;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setAvatar(string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function setBio(string $bio): void
    {
        $this->bio = $bio;
    }

    public function getRememberMeToken(): ?string
    {
        return $this->remember_me_token;
    }

    public function setRememberMeToken(string $remember_me_token): void
    {
        $this->remember_me_token = $remember_me_token;
    }

    public function getRememberMeExpiresAt(): ?string
    {
        return $this->remember_me_expires_at;
    }

    public function setRememberMeExpiresAt(string $remember_me_expires_at): void
    {
        $this->remember_me_expires_at = $remember_me_expires_at;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function getGithub(): ?string
    {
        return $this->github;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function setTwitter(string $twitter): void
    {
        $this->twitter = $twitter;
    }

    public function setFacebook(string $facebook): void
    {
        $this->facebook = $facebook;
    }

    public function setLinkedin(string $linkedin): void
    {
        $this->linkedin = $linkedin;
    }

    public function setGithub(string $github): void
    {
        $this->github = $github;
    }
}
