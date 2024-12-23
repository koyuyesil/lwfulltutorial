<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\Device;
use App\Models\Ticket;
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
        //Device::factory(10)->create();
        //Customer::factory(10)->create();

        Task::factory(10)->create(['user_id' => User::factory()]);

        // Her CustomerDevice için bir aygıt ve bir müşteri oluşturur.
        // CustomerDevice::factory(10)->create([
        //     'device_id' => Customer::factory(),
        //     'customer_id' => Device::factory(),]);


        // Müşteriler oluşturuluyor
        Customer::factory(10)->create()->each(function ($customer) {
            // Her müşteri için cihazlar oluşturuluyor
            $devices = Device::factory(3)->create(); // 3 cihaz oluşturuluyor
            foreach ($devices as $device) {

                // CustomerDevice ilişkisi oluşturuluyor
                $customerDevice = CustomerDevice::factory()->create([
                    'customer_id' => $customer->id,
                    'device_id' => $device->id,

                ]);
                // Her müşteri cihazı için servisler oluşturuluyor
                Ticket::factory(3)->create([
                    'customer_device_id' => $customerDevice->id,
                ]);

            }
        });

    }
}
