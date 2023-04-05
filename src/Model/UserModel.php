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
    private $posts;
    private $comments;
    private $bio;

    public function __construct(
        int $id,
        string $username,
        string $email,
        string $password,
        string $created_at,
        string $role,
        string $avatar,
        array $posts = [],
        array $comments = [],
        string $bio
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->created_at = $created_at;
        $this->role = $role;
        $this->avatar = $avatar;
        $this->posts = $posts;
        $this->comments = $comments;
        $this->bio = $bio;
    }

    public function __toString(): string
    {
        return $this->toJson();
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

    public function getBio(): string
    {
        return $this->bio;
    }

    public function setBio(string $bio): void
    {
        $this->bio = $bio;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'created_at' => $this->created_at,
            'role' => $this->role,
            'avatar' => $this->avatar,
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function getPosts(): array
    {
        return $this->posts;
    }

    public function setPosts(array $posts): void
    {
        $this->posts = $posts;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }
}
