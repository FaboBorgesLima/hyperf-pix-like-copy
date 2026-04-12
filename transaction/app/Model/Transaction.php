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
 * @property string $from_account_id
 * @property string $to_account_id
 * @property string $amount
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Transaction extends Model
{
    use HasUuid;
    use SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'from_account_id',
        'to_account_id',
        'amount',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
