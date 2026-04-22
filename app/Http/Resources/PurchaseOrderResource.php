<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'reference_number' => $this->reference_number,
            'supplier' => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
                'email' => $this->supplier->email,
                'phone' => $this->supplier->phone,
                'address' => $this->supplier->address,
            ],
            'warehouse' => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
                'code' => $this->warehouse->code,
                'address' => $this->warehouse->address,
            ],
            'status' => $this->status,
            'order_date' => $this->order_date,
            'expected_date' => $this->expected_date,
            'received_date' => $this->received_date,
            'tax_rate' => $this->tax_rate,
            'shipping_cost' => $this->shipping_cost,
            'notes' => $this->notes,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'created_by' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->creator->email,
            ],
            'updated_by' => $this->updated_by,
            'items' => PurchaseOrderItemResource::collection($this->items),
            'received_items' => $this->receivedItems,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}