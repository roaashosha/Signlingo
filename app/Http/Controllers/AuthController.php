<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class AuthController extends Controller
{
    use ApiResponseTrait;

    //generate token 
    public function generateToken($token){
        return response()->json([
        'access_token' => $token,
        'token_type'   => 'bearer',
        'expires_in'   => auth('api')->factory()->getTTL() * 60,
        'user' => [
            'id' => auth()->user()->id,
            'name' => auth()->user()->first_name,
            'email' => auth()->user()->email,
        ]
        ]);
    }

    //generate 4 digit otp code
    public function generateOtp(){
        return rand(1000,9999);
    }

    public function register(Request $request){
        //validate the iput
        $validator = Validator::make($request->all(), [
        "name" => "required|string|max:255|unique:users,first_name",
        "email" => "required|email|string",
        "password" => "required|string|min:8|confirmed",
        "agreement"=>"required|accepted"
        ],[
            "agreement.accepted"=>"You must agree to processing of personal data!"
        ]);


        //tell if there is wrong validation
        if($validator->fails()){
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }
        //check if user registered before
        $user = User::where('email',$request->email)->first();
        //if he registered check if hes verified
        if ($user && $user->is_verified){
            return response()->json([
                'message' => 'Email already registered'
            ], 422);
        }
        //if not create a new user
        else if (!$user){
            $user=User::create([
                "first_name"=>$request->name,
                "email"=>$request->email,
                "password"=>Hash::make($request->password),
                "agreement"=>1
            ]);
        }

        //otp creation
        OtpCode::where('user_id', $user->id)->delete();
        $otpCode = $this->generateOtp();
        OtpCode::create([
            "code"=>$otpCode,
            "user_id"=>$user->id,
            'expires_at' => Carbon::now()->addMinutes(5) //expires at 5 mins
        ]);

        //send otp by email
        Mail::to($user->email)->send(new SendOtpMail($otpCode));

        return response()->json([
        "message" => "User registered. Please verify OTP.",
        "user_id" => $user->id
        ], 201);
    }

    //if the user is confirmed with otpcode then jwt it
    public function verifyOtp(Request $request){
        //validate input
        $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'otp' => 'required|digits:4'
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        //check if the otp is assosiated with this user and if its valid or expired
        $otp = OtpCode::where('user_id',$request->user_id)
        ->where('code',$request->otp)
        ->where('expires_at',">=",Carbon::now())
        ->first();

        if (!$otp){
            return $this->ApiResponse(null,"Invalid or expired OTP",400);
        }

        //delete the otp if correct
        $otp->delete();
        //verify the user
        $user = User::find($request->user_id);
        $user->is_verified = 1;
        $user->email_verified_at = Carbon::now();
        $user->save();
        $token = JWTAuth::fromUser($user);
        return $this->ApiResponse(["token"=>$token,"user"=> new UserResource($user)],"User verified successfully!",200);


    }


    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            "email"=>"required|email",
            "password"=>"required|string|min:6",
            "remember_me"=>"boolean"
        ]);

        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        //set remember me for a month
        if ($request->remember_me) {
            JWTAuth::factory()->setTTL(60 * 24 * 30); // 30 days
        //set token for 2 hours
        } else {
            JWTAuth::factory()->setTTL(60 * 2); // 2 hours
        }

        if (! $token = auth('api')->attempt($request->only('email','password'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // return $this->generateToken($token);
        $user = auth('api')->user();
        return $this->ApiResponse(["token"=>$token,'expires_in' => JWTAuth::factory()->getTTL() * 60,"user"=> new UserResource($user)],"User logged in successfully!",200);

    }

    public function logout(){
        auth('api')->logout();
        return $this->ApiResponse(null,"User logout successfully!",200);
    }


    

    

    
}
