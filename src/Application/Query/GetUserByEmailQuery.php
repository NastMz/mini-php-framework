<?php
declare(strict_types=1);

namespace App\Application\Query;

/**
 * GetUserByEmailQuery
 *
 * Query to retrieve a user by their email address.
 */
class GetUserByEmailQuery implements QueryInterface
{
    public function __construct(
        public readonly string $email
    ) {}
}
