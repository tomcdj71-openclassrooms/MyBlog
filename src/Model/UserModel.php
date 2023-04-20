<?php

declare(strict_types=1);

namespace App\Model;

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

    public function __construct(
        int $id,
        string $username,
        string $email,
        string $password,
        string $created_at,
        string $role,
        string $avatar = null,
        string $bio = null,
        string $remember_me_token = null,
        string $remember_me_expires_at = null,
        string $firstName = null,
        string $lastName = null,
        string $twitter = null,
        string $facebook = null,
        string $linkedin = null,
        string $github = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->created_at = $created_at;
        $this->role = $role;
        $this->avatar = $avatar ?? null;
        $this->bio = $bio ?? null;
        $this->remember_me_token = $remember_me_token ?? null;
        $this->remember_me_expires_at = $remember_me_expires_at ?? null;
        $this->firstName = $firstName ?? null;
        $this->lastName = $lastName ?? null;
        $this->twitter = $twitter ?? null;
        $this->facebook = $facebook ?? null;
        $this->linkedin = $linkedin ?? null;
        $this->github = $github ?? null;
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
