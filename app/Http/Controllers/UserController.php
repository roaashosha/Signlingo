<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    use ApiResponseTrait;
    //select and change mode 
    public function selectMode(Request $request){
        //check logged user 
        $user = auth()->user();
        if (!$user){
            return $this->ApiResponse(null,"Unauthenticated user!",401);
        }
        //validate if the choosen mode exists
        $request->validate([
            "mode"=>"required|in:l,a"
        ]);
        //change the mode and save it
        $user->mode = $request->mode;
        $user->save();
        return $this->ApiResponse(["mode"=>$user->mode],"Mode updated Successfully!",200);
        
    }

    //return the username and email only 
    public function userMainData(){
        //check logged user
        $user = auth()->user();
        if (!$user){
            return $this->ApiResponse(null,"Unauthenticated user!",401);
        }
        //return needed data 
        return $this->ApiResponse(["userName"=>$user->first_name,"userEmail"=>$user->email,"userMode"=>$user->mode,"userLang"=>$user->lang],"User data returned Succesfully!",200);

    }

    //return all user profile data
    public function userAllData(){
        //check logged user
        $user=auth()->user();
        if (!$user){
            return $this->ApiResponse(null,"Unauthenticated user!",401);
        }
        //return needed data
        return $this->ApiResponse(["username"=>$user->first_name,"name"=>$user->last_name,"userEmail"=>$user->email,"currentPassword"=>null,"newPassword"=>null,"confirmPassword"=>null],"User data returned Succesfully!",200);
    }

    //update user data
    public function editUser(Request $request)
{
    //Check logged user
    $user = auth()->user();
    if (!$user) {
        return $this->ApiResponse(null, "Unauthenticated user!", 401);
    }

    //Validate inputs
    $request->validate([
        "username"     => "required|string|max:255|unique:users,first_name," . $user->id,
        "name"         => "required|string|max:255",
        "email"        => "required|email|string|unique:users,email," . $user->id,
        "img"          => "nullable|image|mimes:jpg,jpeg,png|max:2048",
        "remove_image" => "nullable|boolean"
    ]);

    //password fields
    $passwordFields = ["current_password", "new_password", "confirm_password"];
    $anyPasswordFilled = false;

    //check if any field is true
    foreach ($passwordFields as $field) {
        if ($request->filled($field)) {
            $anyPasswordFilled = true;
            break;
        }
    }

    //if any field is true, the others should be
    if ($anyPasswordFilled) {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|different:current_password',
            'confirm_password' => 'required|string|same:new_password'
        ]);
        //check if the hashed current password is the same stored
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->ApiResponse(null, "Current password is incorrect!", 400);
        }
        //hash new password
        $user->password = bcrypt($request->new_password);
    }

    //Handle image upload / removal
    if ($request->hasFile('img')) {
        // delete old image if exists
        if ($user->img && Storage::disk('public')->exists($user->img)) {
            Storage::disk('public')->delete($user->img);
        }
        // store new image
        $user->img = $request->file('img')->store('users', 'public');

    } elseif ($request->boolean('remove_image')) {
        // user wants to remove the image
        if ($user->img && Storage::disk('public')->exists($user->img)) {
            Storage::disk('public')->delete($user->img);
        }
        $user->img = null;
    }

    //Update user data
    $user->first_name = $request->username;
    $user->last_name  = $request->name;
    $user->email      = $request->email;
    $user->save();

    //Return image (default if null)
    $imageUrl = $user->img
        ? asset('storage/' . $user->img)
        : asset('images/default-user.png');

    return $this->ApiResponse([
        "username"  => $user->first_name,
        "name"      => $user->last_name,
        "userEmail" => $user->email,
        "image"     => $imageUrl
    ], "User data updated successfully!", 200);
}


    //change the account language
    public function changeLang(Request $request){
        //check if user logged
        $user = auth()->user();
        if (!$user){
            return $this->ApiResponse(null,"Unauthenticated user!",401);
        }
        //validate the choosen language
        $request->validate([
            "lang"=>"required|in:ar,en"
        ]);
        //change in the database and save
        $user->lang = $request->lang;
        $user->save();
        //save it in the app data
        App::setLocale($user->lang);
        return $this->ApiResponse(["langauge"=>$user->lang],"lang updated Successfully!",200);
    }

    //delete user account
    public function deleteUser(){
        //check if the user logged
        $user = auth()->user();
        if (!$user){
            return $this->ApiResponse(null,"Unauthenticated user!",401);
        }
        //delete the user
        $user->delete();
        return $this->ApiResponse(null, "Account deleted successfully!", 200);
    }

    //return the name of the user
    public function getUserName(){
        //check the logged user
        $user = auth()->user();
        if (!$user){
            return $this->ApiResponse(null,"Unauthenticated user!",401);
        }
        //return the name
        return $this->ApiResponse($user->last_name,"User's name returned successfully!",200);
    }
    
    


}
