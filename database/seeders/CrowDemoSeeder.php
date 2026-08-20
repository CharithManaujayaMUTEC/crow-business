<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\SmsSetting;
use Illuminate\Database\Seeder;

class CrowDemoSeeder extends Seeder
{
    public function run(): void
    {
        Customer::firstOrCreate(['phone' => '0770000000'], [
            'name' => 'Demo Customer', 'company_name' => 'Crow Demo Company',
            'email' => 'demo@example.com', 'status' => 'active',
        ]);

        Product::firstOrCreate(['sku' => 'DEMO-001'], [
            'name' => 'Demo Product', 'price' => 10000, 'tax_rate' => 0, 'is_active' => true,
        ]);

        Service::firstOrCreate(['name' => 'Website Maintenance'], [
            'description' => 'Monthly website maintenance service',
            'price' => 25000, 'tax_rate' => 0, 'is_recurring' => true,
            'recurring_interval' => 'monthly', 'is_active' => true,
        ]);

        SmsSetting::firstOrCreate([], [
            'enabled' => false,
            'api_url' => 'https://app.notify.lk/api/v1/send',
            'sender_id' => 'Crow.lk',
            'country_code' => '94',
        ]);
    }
}
