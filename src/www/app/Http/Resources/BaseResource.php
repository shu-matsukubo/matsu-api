<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function with($request): array
    {
        return [
            'success' => true,
        ];
    }
}
