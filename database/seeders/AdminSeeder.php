<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $admin = User::create([
            "first_name"=> "OAMANAGE",
            "name"=> "Admin",
            "name"=> "Super Admin",
            "email"=> "admin@manageoa.com",
            "password"=> Hash::make('&*WPMVMSKlc3CZw#'),
            "role_id"=>1,
            "status"=>1
        ]);
        $admin->assignRole('Admin');

    }
}
