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

    public function __construct(
        int $id,
        string $username,
        string $email,
        string $password,
        string $created_at,
        string $role,
        string $avatar,
        string $bio = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->created_at = $created_at;
        $this->role = $role;
        $this->avatar = $avatar ?? '';
        $this->bio = $bio ?? '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function getBio(): string
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
}
