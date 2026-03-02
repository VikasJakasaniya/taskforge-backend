<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface RepositoryInterface
{
    /**
     * Find a model by its primary key
     */
    public function find(int $id): ?Model;

    /**
     * Find a model by its primary key or fail
     */
    public function findOrFail(int $id): Model;

    /**
     * Get all models
     */
    public function all(): Collection;

    /**
     * Create a new model
     */
    public function create(array $data): Model;

    /**
     * Update a model
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a model
     */
    public function delete(Model $model): bool;

    /**
     * Find a model by specific criteria
     */
    public function findBy(string $field, mixed $value): ?Model;

    /**
     * Find models by specific criteria
     */
    public function findAllBy(string $field, mixed $value): Collection;
}
