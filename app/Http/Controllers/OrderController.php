<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    public function index()
{
    $orders = Order::has('orderDetails')
        ->with(['orderDetails.product', 'user'])
        ->get();

    if ($orders->isEmpty()) {
        return response()->json(['message' => 'No orders found'], 404);
    } else {
        return response()->json($orders, 200);
    }
}
    public function store(Request $request)
    {

        $user = Auth::user();

        $existingOrder = Order::where('customer_id', $user->id)
            ->where('status', '0')
            ->first();

        if ($existingOrder) {
            return response()->json([
                'status' => false,
                'message' => 'Customer already has cart [Inactive order]',
                'order_id' => $existingOrder->id,
            ]);
        }

        $order = Order::create([
            'delivery_address' => $request->delivery_address, // Assuming delivery address is provided in the request
            'customer_id' => $user->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully',
            'order_id' => $order->id,
        ], 201);
    }
    public function show(string $id)
    {
        $order = Order::with('user')->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Order retrieved successfully',
            'order' => $order,
            'customer_name' => $order->user->name,
        ], 200);
    }
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validateUser = Validator::make($request->all(), [
            'delivery_address' => 'sometimes|required|string|max:255',
            'customer_id' => 'sometimes|required|string|max:255|exists:users,id',
        ]);

        if ($validateUser->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validateUser->errors()], 400);
        }

        $order->update($request->all());

        return response()->json(['status' => true, 'message' => 'Order updated successfully', 'user' => $order], 200);
    }
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();

        return response()->json(['status' => true, 'message' => 'Order deleted successfully'], 200);
    }
    public function changeOrderStatusToConfirm(Request $request, $orderId)
    {

        $validator = Validator::make($request->all(), [
            'delivery_address' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 400);
        }

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->orderDetails->isEmpty()) {
            return response()->json(['message' => 'The order cannot be requested: Order is empty'], 400);
        }

        // Update the delivery address
        $order->delivery_address = $request->input('delivery_address');

        $order->status = '1';
        $order->save();

        return response()->json(['message' => 'Order status updated successfully', 'order' => $order], 200);
    }
    public function changeOrderStatusToOne($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($order->orderDetails->isEmpty()) {
            return response()->json(['message' => 'The order cannot be requested: Order is empty'], 400);
        }

        $order->status = '1';
        $order->save();

        return response()->json(['message' => 'Order status updated successfully', 'order' => $order], 200);
    }
    public function changeOrderStatusToTwo($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->status = '2';
        $order->save();

        return response()->json(['message' => 'Order status updated successfully', 'order' => $order], 200);
    }
    public function changeOrderStatusToThree($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->status = '3';
        $order->save();

        return response()->json(['message' => 'Order status updated successfully', 'order' => $order], 200);
    }
    public function getCart(Request $request)
{
    $userId = auth()->id();

    if (!$userId) {
        return response()->json(['message' => 'User not authenticated'], 401);
    }

    $user = User::find($userId);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $order = $user->orders()->where('status', '0')->with(['orderDetails.product','user'])->first();

    if (!$order) {
        return response()->json(['message' => 'No cart exists']);
    }

    $order->orderDetails->each(function ($orderDetail) {
        $orderDetail->product->image_url = url('storage/' . $orderDetail->product->image);
        unset($orderDetail->product->image);
    });

    return response()->json($order);
    }

}
