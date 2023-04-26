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
    private $createdAt;
    private $role;
    private $avatar;
    private $bio;
    private $rememberMeToken;
    private $rememberMeExpiresAt;
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
        $this->createdAt = $userModelParams->createdAt;
        $this->role = $userModelParams->role;
        $this->avatar = $userModelParams->avatar;
        $this->bio = $userModelParams->bio;
        $this->rememberMeToken = $userModelParams->rememberMeToken;
        $this->rememberMeExpiresAt = $userModelParams->rememberMeExpiresAt;
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
        return $this->createdAt;
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

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
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
        return $this->rememberMeToken;
    }

    public function setRememberMeToken(string $rememberMeToken): void
    {
        $this->rememberMeToken = $rememberMeToken;
    }

    public function getRememberMeExpires(): ?string
    {
        return $this->rememberMeExpiresAt;
    }

    public function setRememberMeExpires(string $rememberMeExpiresAt): void
    {
        $this->rememberMeExpiresAt = $rememberMeExpiresAt;
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
