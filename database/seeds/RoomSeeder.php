<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rooms')->insert(
            [
                'room_name' => 'room 1',
                'description' => 'In left side of library',
                'status' => 'Available',
            ]
        );
        DB::table('rooms')->insert(
            [
                'room_name' => 'room 2',
                'description' => 'In right side of library',
                'status' => 'Available',
            ]
        );
        DB::table('rooms')->insert(
            [
                'room_name' => 'room 3',
                'description' => 'In opposite side of library',
                'status' => 'Available',
            ]
        );
    }
}
