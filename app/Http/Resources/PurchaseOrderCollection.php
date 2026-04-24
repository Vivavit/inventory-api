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
        $paginator = $this->resource;

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
                'first' => method_exists($paginator, 'firstPageUrl') ? $paginator->firstPageUrl() : null,
                'last' => method_exists($paginator, 'lastPageUrl') ? $paginator->lastPageUrl() : null,
                'prev' => method_exists($paginator, 'previousPageUrl') ? $paginator->previousPageUrl() : null,
                'next' => method_exists($paginator, 'nextPageUrl') ? $paginator->nextPageUrl() : null,
            ],
            'meta' => [
                'current_page' => method_exists($paginator, 'currentPage') ? $paginator->currentPage() : 1,
                'last_page' => method_exists($paginator, 'lastPage') ? $paginator->lastPage() : 1,
                'per_page' => method_exists($paginator, 'perPage') ? $paginator->perPage() : $this->collection->count(),
                'total' => method_exists($paginator, 'total') ? $paginator->total() : $this->collection->count(),
                'from' => method_exists($paginator, 'firstItem') ? $paginator->firstItem() : null,
                'to' => method_exists($paginator, 'lastItem') ? $paginator->lastItem() : null,
            ],
        ];
    }
}