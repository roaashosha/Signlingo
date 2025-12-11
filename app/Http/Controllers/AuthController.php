<?php

namespace App\Http\Controllers;

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
        'token_type' => 'bearer',
        'expires_in' => auth('api')->factory()->getTTL() * 60,
        'user' => auth()->setToken($token)->user(),
        ]);
    }

    //generate 4 digit otp code
    public function generateOtp(){
        return rand(1000,9999);
    }

    public function register(Request $request){
        //validate the iput
        $validator = Validator::make($request->all(), [
        "name" => "required|string|max:255",
        "email" => "required|email|string|unique:users",
        "password" => "required|string|min:8|confirmed"
        ]);

        //tell if there is wrong validation
        if($validator->fails()){
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        //create a user
        $user=User::create([
            "first_name"=>$request->name,
            "email"=>$request->email,
            "password"=>Hash::make($request->password)
        ]);

        //otp creation
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
        $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'otp' => 'required|digits:6'
        ]);

        if($validator->fails()){
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $otp = OtpCode::where('user_id',$request->user_id)
        ->where('code',$request->otp)
        ->where('expires_at',">=",Carbon::now())
        ->first();

        if (!$otp){
            return $this->ApiResponse(null,"Invalid or expired OTP",400);
        }

        $otp->delete();
        $user = User::find($request->user_id);
        $token = JWTAuth::fromUser($user);
        return $this->generateToken($token);


    }


    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            "email"=>"required|email",
            "password"=>"required|string|min:6"
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        if (! $token = auth()->attempt($validator->validated())){
            return response()->json(['errors'=>"unautherized"],401);
        }
        return $this->createNewToken($token);
    }

    public function logout(){
        auth()->logout();
        return response()->json(["message"=>"user successfuly signed out"]);
    }


    public function rememberMe(){

    }


    

    
}
