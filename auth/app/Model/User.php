<?php

declare(strict_types=1);

namespace App\Model;

use App\Lib\ModelUUID;
use Hyperf\Database\Model\SoftDeletes;

/**
 * @property string $id 
 * @property string $name 
 * @property string $email 
 * @property string $password 
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at 
 * @property \Carbon\Carbon|null $deleted_at 
 */
class User extends ModelUUID
{
    use SoftDeletes;
    /**
     * The table associated with the model.
     */
    protected ?string $table = 'users';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
