<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'Indian Rupee',
                'code' => 'INR',
                'symbol' => '₹',
            ],
            [
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
            ],
            [
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
            ],
            [
                'name' => 'British Pound',
                'code' => 'GBP',
                'symbol' => '£',
            ],
            [
                'name' => 'Japanese Yen',
                'code' => 'JPY',
                'symbol' => '¥',
            ],
            [
                'name' => 'Canadian Dollar',
                'code' => 'CAD',
                'symbol' => 'C$',
            ],
            [
                'name' => 'Australian Dollar',
                'code' => 'AUD',
                'symbol' => 'A$',
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}