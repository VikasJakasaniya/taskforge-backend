<?php

namespace App\Policies;

use App\Models\Import;
use App\Models\User;

class ImportPolicy
{
    /**
     * Determine if the user can view the import.
     */
    public function view(User $user, Import $import): bool
    {
        return $user->id === $import->user_id;
    }

    /**
     * Determine if the user can delete the import.
     */
    public function delete(User $user, Import $import): bool
    {
        return $user->id === $import->user_id;
    }
}
