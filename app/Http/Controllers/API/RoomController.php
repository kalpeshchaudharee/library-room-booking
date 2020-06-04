<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Room;

class RoomController extends Controller
{
    public function roomList() {
        try{
            $rooms = Room::all();
            if(sizeof($rooms) > 0) {
                $success['status'] = 200;
                $success['data'] = $rooms;
                return response()->json(['success' => $success], 200);
            }
        return response()->json(['status' => 404, 'error' => 'Rooms_Not_Found'], 404);
        }catch (\Exception $e) {
            return response()->json(['status' => 500, 'error' => 'Internal_Server_Error'], 500);
        }
    }
    public function roomDetails($id) {
        try{
            $room = Room::find($id);
            if($room) {
                $success['status'] = 200;
                $success['data'] = $room;
                return response()->json(['success' => $success], 200);
            }
        return response()->json(['status' => 404, 'error' => 'Room_Not_Found'], 404);
        }catch (\Exception $e) {
            return response()->json(['status' => 500, 'error' => 'Internal_Server_Error'], 500);
        }
    }
}
