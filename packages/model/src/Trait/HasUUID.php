<?php

declare(strict_types=1);

namespace Shared\Model\Trait;

use Ramsey\Uuid\Uuid;

/**
 * Trait HasUUID
 * If your model needs to use UUID as primary key, you can use this trait to achieve it.
 * @package Shared\Model\Trait
 */
trait HasUUID
{

    protected function initialize(): void
    {
        if (parent::__get("id") === null) {
            parent::__set("id", Uuid::uuid4());
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
