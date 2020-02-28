<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo status($status)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo statusNot($status)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Todo search($search = null)
 */
class Todo extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['todo'];

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

    /**
     * Scope a query to only include todos by status.
     *
     * @param Builder $query
     * @param string $status
     *
     * @return Builder
     */
    public function scopeStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include todos without the status called on parameter.
     *
     * @param Builder $query
     * @param string $status
     *
     * @return Builder
     */
    public function scopeStatusNot(Builder $query, string $status)
    {
        return $query->where('status', '!=', $status);
    }

    /**
     * Scope a query to only include todos matching search.
     *
     * @param Builder $query
     * @param string|null $search
     *
     * @return Builder
     */
    public function scopeSearch(Builder $query, ?string $search = null)
    {
        if (is_null($search)) {
            return $query;
        }

        return $query->where(DB::raw('LOWER(todo)'), 'like', '%'.strtolower($search).'%');
    }
}
