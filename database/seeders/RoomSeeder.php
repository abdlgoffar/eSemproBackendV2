<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $room = new Room();
        $room->name = "Web";
        $room->save();

        $room = new Room();
        $room->name = "Mobile";
        $room->save();

        $room = new Room();
        $room->name = "AI";
        $room->save();

    

    }
}