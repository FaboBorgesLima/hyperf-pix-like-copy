<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

use Carbon\Carbon;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use Shared\Model\Trait\HasUuid;

/**
 * @property string $id
 * @property string $user_id
 * @property string $balance
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Account extends Model
{
    use HasUuid;
    use SoftDeletes;

    public string $keyType = 'string';

    public bool $incrementing = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'accounts';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = ['user_id', 'balance'];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function fromTransactions()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function toTransactions()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }
}
