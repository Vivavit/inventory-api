<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('po_number')
                    ->required(),
                TextInput::make('supplier_id')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'ordered' => 'Ordered',
                        'partially_received' => 'Partially received',
                        'received' => 'Received',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('draft')
                    ->required(),
                DatePicker::make('order_date')
                    ->required(),
                DatePicker::make('expected_delivery_date'),
                DatePicker::make('actual_delivery_date'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping_cost')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
                TextInput::make('warehouse_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
