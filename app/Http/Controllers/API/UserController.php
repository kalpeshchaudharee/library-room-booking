<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User;
use App\OTP;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Mail;
use Validator;
use DB;

class UserController extends Controller 
{
    public $successStatus = 200;
    
    public function login(Request $request) {
        try{
            $validator = Validator::make($request->all(), [ 
                'email' => 'required|email',
                'password' => 'required'
            ]);
            if ($validator->fails()) { 
                return response()->json(['status' => 400, 'error'=>$validator->errors()], 400);            
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
        }catch (\Exception $e) {
            return response()->json(['status' => 500, 'error' => 'Internal_Server_Error'], 500);
        }
    }

     public function forgotPassword(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            if ($validator->fails()) {
               return response()->json(['status' => 400, 'error'=>$validator->errors()], 400);
            }            
            $input = $request->all();
            $user = User::where('email',$input['email'])->first();
            if ($user) {

                $otp = 0;
                $regenCode=true;
                do {
                    $otp = $this->OTPGenerator( 3 );
                    $isOtpExist = OTP::where('code', $otp)->first();
                    if(!$isOtpExist){
                        $regenCode=false;
                        break;
                    }
                }while($regenCode);

                $exp = time() + 15;
                
                $newOTP = OTP::create([
                    'email' => $user->email,
                    'code' => $otp,
                    'status' => 'Active',
                    'expire' => $exp
                ]);

                $body = "Your one time password for reset password is: $otp";
                $email = $user->email;
                Mail::raw($body, function ($msg) use($email) {
                    $msg->to($email);
                });

                $success['status'] = $this->successStatus;
                $success['message'] = 'OTP for reset password sent to: '.$user->email;
                return response()->json(['success' => $success], $this->successStatus);
            }
            return response()->json(['status' => 401, 'error' => 'Invalid_Email'], 401);
        }catch (\Exception $e) {
            return response()->json(['status' => 500, 'error' => 'Internal_Server_Error'], 500);
        }
    }

    public function OTPGenerator( $digits ) {
        $min = pow( 10, $digits );
        $max = pow( 10, $digits+1 )-1;
        $randomCode = rand( $min, $max );
        return $randomCode;
    }

    public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 400, 'error'=>$validator->errors()], 400);
        }
        try {
            $input = $request->all();
            $user = User::where('otp', $input['otp'])->first();
            if ($user) {
                $exp = $user->updated_at + 5400;
                if (time() > $exp) {
                    $user->token = null;
                    $user->save();
                    return response()->json(['status' => 401, 'error' => 'Token_Expired'], 401);
                }
                $user->password = Hash::make($req['password']);
                $user->token = null;
                $user->save();
                $success['status'] = $this->successStatus;
                $success['token'] = $user->createToken('library')->accessToken;
                $success['user'] = $user;
                return response()->json(['success' => $success], $this->successStatus);
            }                
             return response()->json(['status' => 401, 'error' => 'Invalid_Token'], 401);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, 'error' => 'Internal_Server_Error'], 500);
        }
    }
}