<?php

namespace App\Http\Controllers;

use App\Models\image;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;



class ProductController extends Controller
{
    public function index()
{
    $products = Product::where('remaining_quantity', '>=', 1)->get();

    if ($products->isEmpty()) {
        return response()->json(['message' => 'No products found with remaining quantity greater than 1'], 404);
    } else {
        $productData = [];

        foreach ($products as $product) {
            $productData[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'description' => $product->description,
                'image_url' => url('storage/' . $product->image),
                'manufacturer_name' => $product->manufacturer_name,
                'number_of_times_requested' => $product->number_of_times_requested,
                'remaining_quantity' => $product->remaining_quantity,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ];
        }

        return response()->json($productData, 200);
    }
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'category' => 'required|string|max:255',
            'remaining_quantity' => 'required|numeric',
            'description' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,bmp,psd,svg|max:2048',
            'manufacturer_name' => 'required|string|max:255',
            // 'number_of_times_requested' => 'required|integer'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');
        }

        $product = new Product();
        $product->name = $request->input('name');
        $product->price = $request->input('price');
        $product->category = $request->input('category');
        $product->remaining_quantity = $request->input('remaining_quantity');
        $product->description = $request->input('description');
        $product->image = $imagePath;
        $product->manufacturer_name = $request->input('manufacturer_name');
        // $product->number_of_times_requested = $request->input('number_of_times_requested');
        $product->save();

        return response()->json([
            'message' => 'Product created successfully',
            'product' => [
                $productData = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category,
                    'description' => $product->description,
                    'image_url' => url('storage/' . $product->image),
                    'manufacturer_name' => $product->manufacturer_name,
                    // 'number_of_times_requested' => $product->number_of_times_requested,
                    'remaining_quantity' => $product->remaining_quantity,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ]
            ]
        ], 201);
    }
    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $productData = [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
            'description' => $product->description,
            'image_url' => url('storage/' . $product->image),
            'manufacturer_name' => $product->manufacturer_name,
            'number_of_times_requested' => $product->number_of_times_requested,
            'remaining_quantity' => $product->remaining_quantity,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
        ];

        return response()->json($productData, 200);
    }
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validateUser = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'category' => 'sometimes|required|string|max:255',
            'remaining_quantity' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,bmp,psd,svg|max:2048',
            'manufacturer_name' => 'sometimes|required|string|max:255',
            'number_of_times_requested' => 'sometimes|required|integer'
        ]);

        if ($validateUser->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validateUser->errors()], 400);
        }

        $product->update($request->all());

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('images', 'public');
            $product->image = $imagePath;
        }

        return response()->json([
            'status' => true,
            'message' => 'product updated successfully',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'category' => $product->category,
                'description' => $product->description,
                'image_url' => url('storage/' . $product->image),
                'manufacturer_name' => $product->manufacturer_name,
                'number_of_times_requested' => $product->number_of_times_requested,
                'remaining_quantity' => $product->remaining_quantity,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ]
        ], 200);

    }
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'product not found'], 404);
        }

        $product->delete();

        return response()->json(['status' => true, 'message' => 'product deleted successfully'], 200);
    }
    public function updateRequestCount_increase($orderId)
    {
        $order = Order::with('orderDetails.product')->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        foreach ($order->orderDetails as $orderDetail) {
            $product = $orderDetail->product;
            $product->increment('Number_of_times_requested', $orderDetail->quantity);
        }
        return response()->json(['message' => 'updated successfully'], 200);
    }
    public function updateRequestCount_decrease($orderId)
    {
        $order = Order::with('orderDetails.product')->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        foreach ($order->orderDetails as $orderDetail) {
            $product = $orderDetail->product;
            $product->decrement('Number_of_times_requested', $orderDetail->quantity);
        }
        return response()->json(['message' => 'updated successfully'], 200);
    }
    public function updateRemainingQuantity_decrease($orderId)
    {
        $order = Order::with('orderDetails.product')->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        foreach ($order->orderDetails as $orderDetail) {
            $product = $orderDetail->product;
            $product->decrement('remaining_quantity', $orderDetail->quantity);
        }
        return response()->json(['message' => 'updated successfully'], 200);
    }
    public function updateRemainingQuantity_increase($orderId)
    {
        $order = Order::with('orderDetails.product')->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        foreach ($order->orderDetails as $orderDetail) {
            $product = $orderDetail->product;
            $product->increment('remaining_quantity', $orderDetail->quantity);
        }
        return response()->json(['message' => 'updated successfully'], 200);
    }
    public function bestSalary()
    {
        $topFourProducts = Product::orderByDesc('number_of_times_requested')
            ->limit(4)
            ->get();

        if ($topFourProducts->isNotEmpty()) {
            $formattedProducts = $topFourProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category,
                    'description' => $product->description,
                    'image_url' => url('storage/' . $product->image),
                    'manufacturer_name' => $product->manufacturer_name,
                    'number_of_times_requested' => $product->number_of_times_requested,
                    'remaining_quantity' => $product->remaining_quantity,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });

            return response()->json($formattedProducts, 200);
        } else {
            return response()->json(['message' => 'no products exist'], 200);
        }

    }
    public function getCategories()
    {
        $categories = Product::select('Category')->distinct()->pluck('Category');
        return response()->json($categories);
    }
    public function manufacturer_name()
    {
        $categories = Product::select('manufacturer_name')->distinct()->pluck('manufacturer_name');
        return response()->json($categories);
    }

    public function showLatestProducts()
    {
        $latestProducts = DB::table('products')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();
        if ($latestProducts->isNotEmpty()) {
            $productsData = $latestProducts->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category,
                    'description' => $product->description,
                    'image_url' => url('storage/' . $product->image),
                    'manufacturer_name' => $product->manufacturer_name,
                    'number_of_times_requested' => $product->number_of_times_requested,
                    'remaining_quantity' => $product->remaining_quantity,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at,
                ];
            });
            return response()->json($productsData);
        } else {
            return response()->json(['message' => 'no products exist'], 200);
        }



    }

}
