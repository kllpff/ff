<?php

namespace App\Models\Todo;

use FF\Framework\Database\Model;

/**
 * TodoList - Todo list model
 */
class TodoList extends Model
{
    protected string $table = 'todo_lists';
    protected array $fillable = ['user_id', 'title', 'description'];
    protected array $hidden = [];

    /**
     * Get todos in this list
     */
    public function todos()
    {
        return $this->hasMany(Todo::class, 'list_id');
    }

    /**
     * Get completed todos count
     */
    public function getCompletedCount(): int
    {
        return $this->todos()->where('completed', true)->count();
    }

    /**
     * Get pending todos count
     */
    public function getPendingCount(): int
    {
        return $this->todos()->where('completed', false)->count();
    }
}
