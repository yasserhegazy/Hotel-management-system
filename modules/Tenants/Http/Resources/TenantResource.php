<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'tenant_id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'slug' => $this->slug,
            'status' => $this->status,
            'owner_id' => $this->owner_id,
            'location' => new LocationResource($this->whenLoaded('location')),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
