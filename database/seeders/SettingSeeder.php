<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'company_name',    'value' => 'Inventory Management System',          'type' => 'string',  'group' => 'general'],
            ['key' => 'company_address', 'value' => '123 Business Street, Phnom Penh, Cambodia', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_phone',   'value' => '+855 23 456 789',                       'type' => 'string',  'group' => 'general'],
            ['key' => 'company_email',   'value' => 'info@inventory.com',                    'type' => 'string',  'group' => 'general'],

            // Inventory Settings
            ['key' => 'default_low_stock_threshold', 'value' => '10',   'type' => 'number',  'group' => 'inventory'],
            ['key' => 'default_warehouse_id',        'value' => '1',    'type' => 'number',  'group' => 'inventory'],
            ['key' => 'enable_barcode',              'value' => 'true', 'type' => 'boolean', 'group' => 'inventory'],

            // Purchase Settings
            ['key' => 'default_tax_rate', 'value' => '10',  'type' => 'number', 'group' => 'purchase'],
            ['key' => 'po_prefix',        'value' => 'PO-', 'type' => 'string', 'group' => 'purchase'],

            // Report Settings
            ['key' => 'report_currency', 'value' => 'USD',    'type' => 'string', 'group' => 'report'],
            ['key' => 'date_format',     'value' => 'd/m/Y',  'type' => 'string', 'group' => 'report'],
        ];

        foreach ($settings as $setting) {
            // Match on unique 'key', update everything else
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Inventory settings created successfully!');
    }
}
