<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User;
use App\OTP;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Mail;
use Validator;

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

                $exp = date('Y/m/d h:i:s', strtotime("+15 minutes",time()));
                $newOTP = OTP::create([
                    'email' => $user->email,
                    'code' => $otp,
                    'status' => 'Active',
                    'expire' => $exp
                ]);

                // $body = "Your one time password for reset password is: $otp";
                // $email = $user->email;
                // Mail::raw($body, function ($msg) use($email) {
                //     $msg->to($email);
                // });

                $success['status'] = $this->successStatus;
                $success['message'] = 'OTP for reset is: '.$otp;
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
            $otp = OTP::where('code', $input['otp'])->first();
            if ($otp) {
                if($otp->status !== "Active") {
                    return response()->json(['status' => 401, 'error' => 'Invalid_Token'], 401);
                }
                
                if (time() > strtotime($otp->expire)) {
                    $otp->status = "Expired";
                    return response()->json(['status' => 401, 'error' => 'Token_Expired'], 401);
                }
                
                $otp->status = "Verified";
                $otp->save();

                $user = User::where('email', $otp->email)->first();
                $user->password = Hash::make($request['password']);
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