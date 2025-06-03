<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|static where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static static create(array $attributes = [])
 */

class UserDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_name',
        'device_id',
        'device_type',
        'platform',
        'platform_version',
        'browser',
        'browser_version',
        'last_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
