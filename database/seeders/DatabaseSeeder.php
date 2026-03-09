<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@betalent.com'],
            [
                'name' => 'Admin BeTalent',
                'password' => Hash::make('password123'),
            ]
        );

        Gateway::firstOrCreate(
            ['name' => 'Gateway 1'],
            ['is_active' => true, 'priority' => 1]
        );

        Gateway::firstOrCreate(
            ['name' => 'Gateway 2'],
            ['is_active' => true, 'priority' => 2]
        );

        Product::firstOrCreate(
            ['name' => 'Curso Back-end BeTalent'],
            ['amount' => 10000]
        );

        Client::firstOrCreate(
            ['email' => 'cliente@teste.com'],
            ['name' => 'Cliente Teste']
        );
    }
}
