<?php

namespace Database\Seeders;

use App\Models\Buylist;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuyListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Buylist::create([
            'name' => 'Team Buylist',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
