<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Auth extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    public function register(Request $request)
    {
        $validateUser = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|max:15|unique:users',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
        ]);

        if ($validateUser->fails()) {
            return response()->json(['status' => false, 'message' => 'error', 'errors' => $validateUser->errors()], 401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'city' => $request->city,
            'status' => 'customer'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken,
            'user' => [$user],
        ], 200);
    }

    public function login(Request $request){
        $cred = $request->only('email','password');
        $user = User::where('email',$cred['email'])->first();
        if(!$user || !Hash::check($cred['password'],$user->password)){
            return $this->error('','not match',401);
        }
        return response()->json([
                    'status' => true,
                    'message' => 'عند مين بتحلق',
                    'token' => $user->createToken("API TOKEN")->plainTextToken,
                    'user' => [$user]
                ], 200);
    }
}
