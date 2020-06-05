<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            if(strtotime($input['start_time']) < time()) {
                return response()->json(['status' => 400, 'error' => 'Invalid Date and time'], 400);
            }
            if(date('m/d/Y', strtotime($input['start_time'])) > date('m/d/Y', time())) {
                return response()->json(['status' => 400, 'error' => 'Invalid Date'], 400);
            }

            $end_time = date('Y/m/d h:i:s',strtotime("+3 hours" ,strtotime($input['start_time'])));
            $user = Auth::user();
            $room = Room::find($roomId);
            
            if($room) {
                $checkUserBooking = Booking::where('user_id', $user->id)->latest('end_time')->first();
                if($checkUserBooking) {
                    if(strtotime(date('Y/m/d h:i:s', strtotime($input['start_time']))) < strtotime("+4 hours" ,strtotime($checkUserBooking->end_time))) {
                        return response()->json(['status' => 400, 'error' => 'Room can be booked after 4 hours of previous booking'], 400);
                    }
                }

                $checkBooking = Booking::where('room_id', $room->id)->whereBetween('start_time', [date('Y/m/d', time()).' 00:00:00', date('Y/m/d', time()).' 23:59:50'])->get();
                foreach($checkBooking as $b) {
                    if(strtotime($b->end_time) > strtotime($input['start_time'])) {
                        if(strtotime($b->start_time) < strtotime($input['start_time'])) {
                            return response()->json(['status' => 400, 'error' => 'Room not available'], 400);
                        }
                    }
                }

                $booking = Booking::create([
                    'user_id' => $user->id,
                    'room_id' => $room->id,
                    'start_time' => date('Y/m/d h:i:s',strtotime($input['start_time'])),
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
    
    public function bookingList() {
        try{
            $bookings = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->get();

            foreach($bookings as $booking) {
                unset($booking->password);
            }

            $success['status'] = 200;
            $success['data'] = $bookings;
            return response()->json(['success' => $success], 200);
        }catch (\Exception $e) {
            return response()->json(['status' => 500, 'error' => 'Internal_Server_Error'], 500);
        }
    }
}
