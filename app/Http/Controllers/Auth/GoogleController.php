<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class GoogleController extends Controller
{
    public function AuthWithGoogle(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = $request->id_token;
        $clientId = env('GOOGLE_CLIENT_ID');

        // Verify token with Google
        $client = new Client();
        $response = $client->get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");
        $data = json_decode($response->getBody(), true);

        if (!isset($data['aud']) || $data['aud'] !== $clientId) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        // Check if user exists
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'google_id' => $data['sub'],
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        // Optional: log in user (for session-based API)
        Auth::login($user);

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => $user
        ]);
    }
}
