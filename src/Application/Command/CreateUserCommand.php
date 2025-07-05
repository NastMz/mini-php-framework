<?php
declare(strict_types=1);

namespace App\Application\Command;

/**
 * CreateUserCommand
 *
 * Command to create a new user in the system.
 */
class CreateUserCommand implements CommandInterface
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly string $password
    ) {}
}
