<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use Hyperf\DbConnection\Model\Model;
use Shared\Model\Trait\HasUuid;

/**
 * @property string      $id
 * @property string      $type         email | sms | push
 * @property string      $recipient
 * @property string      $subject
 * @property string      $body
 * @property string      $status       pending | sent | failed
 * @property array       $metadata
 * @property string|null $error_message
 * @property Carbon|null $sent_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class Notification extends Model
{
    use HasUuid;

    public const STATUS_PENDING = 'pending';
    public const STATUS_SENT    = 'sent';
    public const STATUS_FAILED  = 'failed';

    public string $keyType    = 'string';
    public bool   $incrementing = false;

    protected ?string $table = 'notifications';

    protected array $fillable = [
        'type',
        'recipient',
        'subject',
        'body',
        'status',
        'metadata',
        'error_message',
        'sent_at',
    ];

    protected array $casts = [
        'metadata'   => 'array',
        'sent_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
