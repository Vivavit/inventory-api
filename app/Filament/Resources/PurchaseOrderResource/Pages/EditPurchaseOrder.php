<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
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
