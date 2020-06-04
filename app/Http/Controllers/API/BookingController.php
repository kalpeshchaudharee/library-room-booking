<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Booking;
use App\User;
use App\Room;
use Validator;

class BookingController extends Controller
{
    public function createBooking(Request $request, $roomId) {
        try{
            $validator = Validator::make($request->all(), [
                'start_time' => 'required|date'
            ]);
            if ($validator->fails()) { 
                return response()->json(['status' => 400, 'error'=>$validator->errors()], 400);            
            }
            $input = $request->all();
            return date('m/d/Y h:m:s',strtotime("+10 minutes" ,strtotime($input['start_time'])));
            $user = Auth::user();
            $room = Room::find($roomId);
            if($room) {
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'room_id' => $room->id,
                    'start_time' => $input['start_time'],
                    'end_time' => $end_time
                ]);
                $success['status'] = 200;
                $success['data'] = $booking;
                return response()->json(['success' => $success], 200);
            }
            return response()->json(['status' => 404, 'error' => 'Room_Not_Found'], 404);
        }catch (\Exception $e) {
            return response()->json(['status' => 500, 'error' => 'Internal_Server_Error'], 500);
        }
    }
}
