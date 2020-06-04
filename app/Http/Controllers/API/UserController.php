<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Validator;
use DB;

class UserController extends Controller 
{
    public $successStatus = 200;
    
    public function login(Request $request) { 
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 401);            
        }
        $input = $request->all();
        $user = User::where('email',$input['email'])->first();
        if(isset($user) && (Hash::check($input['password'], $user->password))) {
            $success['status'] = $this->successStatus;
            $success['user'] = $user;
            $success['token'] = $user->createToken('library')->accessToken;
            return response()->json(['success' => $success], $this->successStatus); 
        }
        return response()->json(['status' => 401, 'error' => 'Unauthorised'], 401);  
    }
}