<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse?->name,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) ($this->discount_amount ?? 0),
            'tax_amount' => (float) ($this->tax_amount ?? 0),
            'total' => (float) $this->total,
            'status' => $this->status,
            'notes' => $this->notes,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}