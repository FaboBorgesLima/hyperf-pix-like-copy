<?php

namespace App\Lib;

use Ramsey\Uuid\Uuid;
use Hyperf\DbConnection\Model\Model;

abstract class ModelUUID extends Model
{
    protected string $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public bool $incrementing = false;

    protected function initialize(): void
    {
        if (!$this->id) {
            $this->id = (string) Uuid::uuid4();
        }
    }

    public function save(array $options = []): bool
    {
        $this->initialize();
        return parent::save($options);
    }

    public static function create(array $attributes = [])
    {
        $model = new static($attributes);
        $model->initialize();
        $model = $model->save() ? $model : null;
        return $model;
    }
}
