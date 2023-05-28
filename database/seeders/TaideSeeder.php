<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class TaideSeeder extends Seeder
{
    /**
     * This create the password hash
     */
    public function run(): void
    {
        dump(Hash::make("wuulong"));
    }
}
