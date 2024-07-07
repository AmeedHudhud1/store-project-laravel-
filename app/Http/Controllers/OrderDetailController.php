<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderDetailController extends Controller
{
    public function create()
    {
        //
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|integer',
        'product_id' => 'required|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => 'Error', 'errors' => $validator->errors()], 400);
    }

    $quantity = $request->input('quantity', 1);

    // Retrieve the product
    $product = Product::find($request->input('product_id'));

    if (!$product) {
        return response()->json(['status' => false, 'message' => 'Product not found'], 404);
    }

    // Check if the requested quantity is less than or equal to the remaining quantity of the product
    if ($quantity > $product->remaining_quantity) {
        return response()->json(['status' => false, 'message' => 'Requested quantity exceeds remaining quantity of the product'], 400);
    }
    // dd($product->remaining_quantity);

    $existingDetail = OrderDetail::where('order_id', $request->input('order_id'))
        ->where('product_id', $request->input('product_id'))
        ->first();

    if ($existingDetail) {
        // Calculate total quantity after update
        $newQuantity = $existingDetail->quantity + $quantity;

        // Check if the updated quantity exceeds remaining quantity
        if ($newQuantity > $product->remaining_quantity) {
            return response()->json(['status' => false, 'message' => 'Requested quantity exceeds remaining quantity of the product'], 400);
        }

        // Update the quantity
        $existingDetail->quantity = $newQuantity;
        $existingDetail->save();

        return response()->json([
            'message' => 'Quantity updated successfully',
            'order_details' => [
                'order_id' => $existingDetail->order_id,
                'product_id' => $existingDetail->product_id,
                'quantity' => $existingDetail->quantity,
            ]
        ], 200);
    }

    // Create new order detail
    $details = new OrderDetail();
    $details->order_id = $request->input('order_id');
    $details->product_id = $request->input('product_id');
    $details->quantity = $quantity;
    $details->save();

    return response()->json([
        'message' => 'Created successfully',
        'order_details' => [
            'order_id' => $details->order_id,
            'product_id' => $details->product_id,
            'quantity' => $details->quantity,
        ]
    ], 201);
}


    public function update(Request $request, string $orderId, string $productId)
{
    $details = OrderDetail::where('order_id', $orderId)->where('product_id', $productId)->first();

    if (!$details) {
        return response()->json(['message' => 'Order detail not found'], 404);
    }

    // Validate the input
    $validate = Validator::make($request->all(), [
        'quantity' => 'required|integer', // Ensure quantity is at least 1
    ]);

    if ($validate->fails()) {
        return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validate->errors()], 400);
    }

    // Increment the quantity
    $details->quantity += $request->input('quantity');
    $details->save();

    return response()->json(['status' => true, 'message' => 'Quantity updated successfully', 'order_details' => $details], 200);
}


    public function getOrderDetails($orderId)
    {
        $order = Order::with('orderDetails.product')->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->orderDetails->each(function ($orderDetail) {
            $orderDetail->product->image_url = url('storage/' . $orderDetail->product->image);
            unset($orderDetail->product->image);
        });

        return response()->json($order);
    }

    public function deleteProductFromOrder($order_id, $product_id)
    {
        $orderDetail = OrderDetail::where('order_id', $order_id)
            ->where('product_id', $product_id)
            ->first();

        if (!$orderDetail) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $orderStatus = $orderDetail->order->status;

        if ($orderStatus != 0) {
            return response()->json(['message' => 'Order is active, product cannot be deleted'], 403);
        }

        $orderDetail->delete();

        return response()->json(['message' => 'Product deleted from order successfully'], 200);
    }

    public function resetCart(Request $request)
    {
        $user_id = auth()->id(); // Retrieve the user ID from the authenticated user

        if (!$user_id) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $orders = Order::where('customer_id', $user_id)
            ->where('status', "0")
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No cart exists'], 404);
        }

        foreach ($orders as $order) {
            OrderDetail::where('order_id', $order->id)->delete();
        }

        return response()->json(['message' => 'Cart reset successfully'], 200);
    }



}
