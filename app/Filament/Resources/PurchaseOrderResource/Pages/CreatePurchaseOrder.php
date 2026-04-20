<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate PO number
        if (empty($data['po_number'])) {
            $data['po_number'] = PurchaseOrder::generatePONumber();
        }

        // Set created_by to current authenticated user
        $data['created_by'] = auth()->id();

        // Calculate totals
        $items = $data['items'] ?? [];
        $subtotal = 0;

        foreach ($items as &$item) {
            $itemTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $item['total_price'] = $itemTotal;
            $subtotal += $itemTotal;
        }

        $data['subtotal'] = $subtotal;
        $data['total_amount'] = $subtotal + ($data['tax_amount'] ?? 0) + ($data['shipping_cost'] ?? 0);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
