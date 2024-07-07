<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['login', 'store']]);
    // }


    public function index()
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found'], 404);
        } else {
            return response()->json($users, 200);
        }
    }

    // public function store(Request $request)
    // {
    //     $validateUser = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8',
    //         'phone_number' => 'required|string|max:15|unique:users',
    //         'address' => 'required|string|max:255',
    //         'city' => 'required|string|max:255',
    //     ]);

    //     if ($validateUser->fails()) {
    //         return response()->json(['status' => false, 'message' => 'error', 'errors' => $validateUser->errors()], 401);
    //     }

    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'phone_number' => $request->phone_number,
    //         'address' => $request->address,
    //         'city' => $request->city,
    //         'status' => 'customer'
    //     ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'successfully',
    //         'token' => $user->createToken("API TOKEN")->plainTextToken,
    //         'user' => [$user],
    //     ], 200);
    // }

    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'User retrieved successfully',
            'user' => $user,
        ], 200);

    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validateUser = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
            'phone_number' => 'sometimes|required|string|max:15|unique:users,phone_number,' . $user->id,
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:255',
        ]);

        if ($validateUser->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validateUser->errors()], 400);
        }

        $user->update($request->all());

        return response()->json(['status' => true, 'message' => 'User updated successfully', 'user' => $user], 200);
    }

    public function destroy(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['status' => true, 'message' => 'User deleted successfully'], 200);
    }
    public function showUsingEmail(string $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'User retrieved successfully',
            'user' => $user,
        ], 200);
    }

    public function profile()
    {

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'User retrieved successfully',
            'user' => $user,
        ], 200);
    }
    public function orders(Request $request, $userId)
{
    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $order = $user->orders()->where('status', '!=', '0')->with(['orderDetails.product'])->get();

    if ($order->isEmpty()) {
        return response()->json(['message' => 'No orders exist'], 200);
    }

    $order->each(function ($orderItem) {
        $orderItem->orderDetails->each(function ($orderDetail) {
            $orderDetail->product->image_url = url('storage/' . $orderDetail->product->image);
            unset($orderDetail->product->image);
        });
    });

    return response()->json($order);
}










    // public function login(Request $request)
    // {
    //     $validateUser = Validator::make($request->all(), [
    //         'email' => 'required',
    //         'password' => 'required'
    //     ]);

    //     if ($validateUser->fails()) {
    //         return response()->json(['status' => false, 'errors' => $validateUser->errors()], 401);
    //     }

    //     if (!Auth::attempt($request->only(['email', 'password']))) {
    //         return response()->json(['status' => false, 'message' => 'error in validation'], 401);
    //     }

    //     $user = User::where('email', $request->email)->first();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'عند مين بتحلق',
    //         'token' => $user->createToken("API TOKEN")->plainTextToken,
    //         'user' => [$user]
    //     ], 200);
    // }


    // public function login(Request $request){
    //     $cred = $request->only('email','password');
    //     $user = User::where('email',$cred['email'])->first();
    //     if(!$user || !Hash::check($cred['password'],$user->password)){
    //         return $this->error('','not match',401);
    //     }
    //     return response()->json([
    //                 'status' => true,
    //                 'message' => 'عند مين بتحلق',
    //                 'token' => $user->createToken("API TOKEN")->plainTextToken,
    //                 'user' => [$user]
    //             ], 200);
    // }
}
