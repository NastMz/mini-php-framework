<?php
declare(strict_types=1);

namespace App\Domain\Model;

use App\Infrastructure\Serialization\Attributes\JsonSerializable;
use App\Infrastructure\Serialization\Attributes\JsonProperty;
use App\Infrastructure\Serialization\Attributes\JsonIgnore;
use App\Infrastructure\Persistence\Mapping\Table;
use App\Infrastructure\Persistence\Mapping\Column;
use DateTime;

/**
 * User model with automatic serialization
 */
#[Table(name: 'users')]
#[JsonSerializable(exclude: ['password_hash'], camelCase: true)]
class User
{
    #[Column(name: 'id', id: true, auto: true)]
    private ?int $id = null;

    #[Column(name: 'name')]
    private string $name;

    #[Column(name: 'email')]
    private string $email;

    #[Column(name: 'password_hash')]
    #[JsonIgnore(serialize: true)] // Never serialize password
    private string $passwordHash;

    #[Column(name: 'created_at')]
    #[JsonProperty(name: 'created_at', format: 'datetime')]
    private DateTime $createdAt;

    #[Column(name: 'updated_at')]
    #[JsonProperty(name: 'updated_at', format: 'datetime')]
    private DateTime $updatedAt;

    #[Column(name: 'email_verified_at')]
    #[JsonProperty(name: 'email_verified_at', format: 'datetime')]
    private ?DateTime $emailVerifiedAt = null;

    public function __construct(string $name, string $email, string $passwordHash)
    {
        $this->name = $name;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getEmailVerifiedAt(): ?DateTime
    {
        return $this->emailVerifiedAt;
    }

    // Setters
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTime();
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updatedAt = new DateTime();
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
        $this->updatedAt = new DateTime();
    }

    public function setEmailVerifiedAt(?DateTime $emailVerifiedAt): void
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->updatedAt = new DateTime();
    }

    // Helper methods
    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function verifyEmail(): void
    {
        $this->emailVerifiedAt = new DateTime();
        $this->updatedAt = new DateTime();
    }
}
