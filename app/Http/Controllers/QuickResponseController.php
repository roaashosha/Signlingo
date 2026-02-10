<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuickResponseResource;
use App\Models\QuickResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuickResponseController extends Controller
{
    use ApiResponseTrait;
    public function getResponses(){
        $userId = auth()->id();
        $quickResponses = QuickResponse::where('user_id',$userId)->get();
        if (!$quickResponses){
            return $this->ApiResponse(null,"No quick responses were found",404);
        }
        return $this->ApiResponse(QuickResponseResource::collection($quickResponses),"Quick responses returned succesfully!",200);
    }

    public function generateResponse(Request $request)
    {
        $userId = auth()->id();

        $request->validate([
            'title.en' => 'required|string|max:255',
            'title.ar' => 'required|string|max:255',
            'audio' => 'required|file|mimes:mp3,wav,ogg|max:10240', // max 10MB
        ]);

        $title = $request->input('title'); // ['en' => 'English', 'ar' => 'Arabic']
        $audioFile = $request->file('audio');

        // Save audio in public/sound
        $fileName = Str::uuid() . '.' . $audioFile->getClientOriginalExtension();
        $audioFile->move(public_path('sound'), $fileName);

        // Save record in DB
        $quickResponse = QuickResponse::create([
            'title' => json_encode($title, JSON_UNESCAPED_UNICODE) ,
            'sound' => $fileName,
            'user_id' => $userId,
        ]);

        return $this->ApiResponse(
            new QuickResponseResource($quickResponse),
            "Audio uploaded successfully!",
            201
        );

    }

    public function deleteResponse(Request $request,$id){
        $userId = auth()->id();

    // Find the response that belongs to this user
    $quickResponse = QuickResponse::where('id', $id)
        ->where('user_id', $userId)
        ->first();

    if (!$quickResponse) {
        return $this->ApiResponse(null, "Quick response not found", 404);
    }

    // Delete the audio file if it exists
    $filePath = public_path($quickResponse->sound); // e.g., public/sound/filename.mp3
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Delete record from DB
    $quickResponse->delete();

    return $this->ApiResponse(null, "Quick response deleted successfully", 200);

    }
}
