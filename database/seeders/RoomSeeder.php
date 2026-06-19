<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            ['room_no' => '101', 'type' => 'Standard', 'floor' => 1, 'price_night' => 5000, 'status' => 'Available'],
            ['room_no' => '102', 'type' => 'Standard', 'floor' => 1, 'price_night' => 5000, 'status' => 'Occupied'],
            ['room_no' => '103', 'type' => 'Deluxe', 'floor' => 1, 'price_night' => 8000, 'status' => 'Cleaning'],
            ['room_no' => '201', 'type' => 'Standard', 'floor' => 2, 'price_night' => 5500, 'status' => 'Available'],
            ['room_no' => '202', 'type' => 'Suite', 'floor' => 2, 'price_night' => 15000, 'status' => 'Maintenance'],
            ['room_no' => '203', 'type' => 'Deluxe', 'floor' => 2, 'price_night' => 8500, 'status' => 'Available'],
            ['room_no' => '301', 'type' => 'Suite', 'floor' => 3, 'price_night' => 20000, 'status' => 'Available'],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(['room_no' => $room['room_no']], $room);
        }
    }
}
