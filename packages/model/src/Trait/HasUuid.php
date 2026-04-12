<?php

declare(strict_types=1);

namespace Shared\Model\Trait;

use Hyperf\Database\Model\Builder;

use Hyperf\Database\Model\Concerns\HasUuids;

trait HasUuid
{
    use HasUuids;
    /**
     * Generate UUIDs for unique-id columns before inserting.
     */
    protected function performInsert(Builder $query): bool
    {
        foreach ($this->uniqueIds() as $column) {
            if (empty($this->getAttribute($column))) {
                $this->setAttribute($column, $this->newUniqueId());
            }
        }

        return parent::performInsert($query);
    }
}
