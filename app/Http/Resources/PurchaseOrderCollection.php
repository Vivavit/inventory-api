<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Purchase orders retrieved successfully',
            'data' => $this->collection->map(function ($purchaseOrder) {
                return [
                    'id' => $purchaseOrder->id,
                    'po_number' => $purchaseOrder->po_number,
                    'reference_number' => $purchaseOrder->reference_number,
                    'supplier' => [
                        'id' => $purchaseOrder->supplier->id,
                        'name' => $purchaseOrder->supplier->name,
                    ],
                    'warehouse' => [
                        'id' => $purchaseOrder->warehouse->id,
                        'name' => $purchaseOrder->warehouse->name,
                    ],
                    'status' => $purchaseOrder->status,
                    'order_date' => $purchaseOrder->order_date,
                    'expected_date' => $purchaseOrder->expected_date,
                    'subtotal' => $purchaseOrder->subtotal,
                    'total_amount' => $purchaseOrder->total_amount,
                    'created_by' => [
                        'id' => $purchaseOrder->creator->id,
                        'name' => $purchaseOrder->creator->name,
                    ],
                    'created_at' => $purchaseOrder->created_at,
                ];
            }),
            'links' => [
                'first' => $this->collection->firstPageUrl(),
                'last' => $this->collection->lastPageUrl(),
                'prev' => $this->collection->prevPageUrl(),
                'next' => $this->collection->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $this->collection->currentPage(),
                'last_page' => $this->collection->lastPage(),
                'per_page' => $this->collection->perPage(),
                'total' => $this->collection->total(),
                'from' => $this->collection->from(),
                'to' => $this->collection->to(),
            ],
        ];
    }
}