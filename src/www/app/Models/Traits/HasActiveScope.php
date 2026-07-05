<?php

namespace App\Models\Traits;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Builder;

trait HasActiveScope
{
    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            /** @phpstan-ignore argument.type */
            $this->getTable().'.is_active',
            ActiveStatus::ACTIVE
        );
    }
}
