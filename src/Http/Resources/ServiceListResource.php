<?php

namespace Esanj\AppService\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'name' => $this->name,
            'client_id' => $this->client_id,
            'status' => $this->is_active,
            'deleted_at' => $this->deleted_at
        ];
    }
}
