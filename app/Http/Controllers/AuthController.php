<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Subscription;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $data['fullName'] = collect(explode(' ', $data['fullName']))
            ->map(fn($word) => ucfirst(strtolower($word)))
            ->join(' ');

        $user = User::create([
            'name' => $data['fullName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('main')->plainTextToken;

        Auth::login($user);

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function login(Request $request)
    {

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            $token = $user->createToken('main')->plainTextToken;
            return response()->json(['message' => 'Login successful', 'token' => $token, 'user' => new UserResource($user),]);
        }
        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }

    public function getLoggedInUser()
    {
        $user = Auth::user();

        $subscription = Subscription::where('user_id', $user->id)->where('status', 'active')->first();

        return (new UserResource($user))->additional([
            'subscription' => $subscription,
        ]);
    }

}
