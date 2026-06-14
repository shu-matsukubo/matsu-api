<?php

namespace App\Models\Traits;

use App\Enums\ActiveStatus;

trait HasActiveScope
{
    public function scopeActive($query)
    {
        return $query->where(
            $this->getTable().'.is_active',
            ActiveStatus::ACTIVE->value
        );
    }
}
