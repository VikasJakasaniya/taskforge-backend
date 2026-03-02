<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by priority
     */
    public function scopePriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for filtering by due date range
     */
    public function scopeDueDateBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('due_date', [$from, $to]);
    }

    /**
     * Scope for searching in title and description
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for sorting
     */
    public function scopeSort(Builder $query, string $field, string $direction = 'asc'): Builder
    {
        $allowedFields = ['title', 'status', 'priority', 'due_date', 'created_at'];

        if (in_array($field, $allowedFields)) {
            return $query->orderBy($field, $direction);
        }

        return $query;
    }
}
