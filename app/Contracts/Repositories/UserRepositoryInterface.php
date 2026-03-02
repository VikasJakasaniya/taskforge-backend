<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find a user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find or create a user by email
     */
    public function firstOrCreate(string $email, array $data = []): User;
}
