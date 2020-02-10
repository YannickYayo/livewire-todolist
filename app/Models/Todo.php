<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Todo.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $todo
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo whereTodo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Todo extends Model
{
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if todo is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status == 'completed';
    }
}
