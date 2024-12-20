<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Device;
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
        Device::factory(10)->create();
        Customer::factory(10)->create();
        CustomerDevice::factory(10)->create([
            'device_id' => Customer::factory(),
            'customer_id' => Device::factory(),]);

        //Service::factory(10)->create(['customer_id'=>Customer::factory()]);

    }
}
