<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->computeStatus(),
            'user_id' => $this->user_id,
            'primary_color' => $this->primary_color ?? '#3B82F6',
            'secondary_color' => $this->secondary_color ?? '#EFF6FF',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'submissions_count' => $this->submissions_count ?? $this->submissions()->count(),
            'settings' => $this->settings ?? null,
        ];
    }
}