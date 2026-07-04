<?php

namespace App\Models\Traits;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasActiveScope
{
    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        /** @phpstan-ignore argument.type */
        return $query->where(
            $this->getTable().'.is_active',
            ActiveStatus::ACTIVE->value
        );
    }
}
