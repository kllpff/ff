<?php

namespace App\Models\Todo;

use FF\Framework\Database\Model;

/**
 * Todo - Todo item model
 */
class Todo extends Model
{
    protected string $table = 'todos';
    protected array $fillable = ['list_id', 'title', 'description', 'priority', 'due_date', 'completed'];
    protected array $hidden = [];

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    /**
     * Get parent list
     */
    public function list()
    {
        return $this->belongsTo(TodoList::class, 'list_id');
    }

    /**
     * Mark as completed
     */
    public function complete(): void
    {
        $this->update(['completed' => true]);
    }

    /**
     * Mark as incomplete
     */
    public function incomplete(): void
    {
        $this->update(['completed' => false]);
    }

    /**
     * Check if overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date && !$this->completed && $this->due_date < date('Y-m-d');
    }

    /**
     * Get high priority todos
     */
    public static function highPriority()
    {
        return static::where('priority', self::PRIORITY_HIGH)
            ->where('completed', false)
            ->orderBy('due_date', 'asc');
    }

    /**
     * Get pending todos (not completed)
     */
    public static function pending()
    {
        return static::where('completed', false)
            ->orderBy('due_date', 'asc');
    }
}
