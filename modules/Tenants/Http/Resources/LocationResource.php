<?php

declare(strict_types=1);

namespace Modules\Tenants\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
            'region_name' => $this->region_name,
            'city' => $this->city,
            'address_line' => $this->address_line,
            'postal_code' => $this->postal_code,
            'timezone' => $this->timezone,
        ];
    }
}
