<?php

namespace App\Repositories;

use App\Contracts\Repositories\ImportRepositoryInterface;
use App\Models\Import;
use Illuminate\Database\Eloquent\Collection;

class ImportRepository extends BaseRepository implements ImportRepositoryInterface
{
    public function __construct(Import $model)
    {
        parent::__construct($model);
    }

    public function createForUser(int $userId, array $data): Import
    {
        return $this->create([
            'user_id' => $userId,
            ...$data
        ]);
    }

    public function getImportsForUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
