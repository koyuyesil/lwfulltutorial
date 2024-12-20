<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use App\Models\Task;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //User::factory(10)->create();
        //Task::factory(10)->create();
        Task::factory(10)->create(['user_id' => User::factory()]);
        Customer::factory(10)->create();
        //Service::factory(10)->create(['customer_id'=>Customer::factory()]);

    }
}
