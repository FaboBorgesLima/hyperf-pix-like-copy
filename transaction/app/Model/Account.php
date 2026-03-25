<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\SoftDeletes;
use Shared\Model\Trait\HasUUID;

/**
 * @property string $id 
 * @property string $user_id 
 * @property string $balance 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Account extends Model
{
    use HasUUID, SoftDeletes;

    public string $keyType = 'string';
    public bool $incrementing = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ["user_id", "balance"];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
}
