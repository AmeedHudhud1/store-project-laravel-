<?php

namespace App\Http\Controllers;

use App\Models\FavoriteProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class FavoriteProductController extends Controller
{
    public function Favorite(Request $request)
{
    // Retrieve the authenticated user
    $user = auth()->user();

    // Validate the request
    $request->validate([
        'product_id' => 'required|exists:products,id',
    ]);

    // Check if the product is already favorited by the user
    $favorite = FavoriteProduct::where('customer_id', $user->id)
                              ->where('product_id', $request->product_id)
                              ->first();

    if ($favorite) {
        $favorite->delete();
        return response()->json(['message' => 'Product removed from favorites.'], 200);
    } else {
        FavoriteProduct::create([
            'customer_id' => $user->id,
            'product_id' => $request->product_id,
        ]);
        return response()->json(['message' => 'Product added to favorites.'], 201);
    }
}

public function getAllFavoritesForUser()
{
    $user = auth()->user();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Retrieve favorite products for the authenticated user
    $favoriteProducts = FavoriteProduct::where('customer_id', $user->id)
                            ->with('product')
                            ->get();
    // Check if favorite products exist for the user
    if ($favoriteProducts->isEmpty()) {
        return response()->json(['message' => 'No favorite products found for user']);
    }

    // Format the response data
    $formattedFavorites = $favoriteProducts->map(function ($favorite) {
        $product = $favorite->product;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'category' => $product->category,
            'description' => $product->description,
            'image_url' => url('storage/' . $product->image), // Assuming image is stored in storage/app/public/
            'manufacturer_name' => $product->manufacturer_name,
            'remaining_quantity' => $product->remaining_quantity,
            'created_at' => $product->created_at->format('Y-m-d H:i:s'), // Format as needed
            'updated_at' => $product->updated_at->format('Y-m-d H:i:s'), // Format as needed
        ];
    });

    // Return the formatted favorites as JSON response
    return response()->json(['favorite_products' => $formattedFavorites], 200);
}

public function deleteAllFavoritesForUser()
{
    $userId = auth()->id(); // Get the authenticated user's ID

    $user = User::find($userId);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $favoriteCount = FavoriteProduct::where('customer_id', $userId)->count();
    if ($favoriteCount == 0) {
        return response()->json(['message' => 'No favorite products found for user'], 404);
    }

    FavoriteProduct::where('customer_id', $userId)->delete();

    return response()->json(['message' => 'All favorite products deleted for user'], 200);
}


    public function addFavoriteProductsToCart(Request $request)
{
    $user = Auth::user();

    // Step 1: Retrieve all favorite products for the user
    $favoriteProducts = FavoriteProduct::where('customer_id', $user->id)->get();

    // Step 2: Check if there is an existing active order for the user
    $existingOrder = Order::where('customer_id', $user->id)
        ->where('status', '0')
        ->first();

    if (!$existingOrder) {
        // If no active order exists, create a new order
        $order = Order::create([
            'delivery_address' => $request->delivery_address, // Assuming delivery address is provided in the request
            'customer_id' => $user->id,
        ]);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
            ], 500);
        }
    } else {
        $order = $existingOrder;
    }

    // Step 3: Add each favorite product to the order
    foreach ($favoriteProducts as $favoriteProduct) {
        $product = Product::find($favoriteProduct->product_id);

        if ($product) {
            // Check if the product can be added to the order (e.g., remaining quantity check)
            if ($favoriteProduct->product_id && $product->remaining_quantity > 0) {
                // Check if the product is already in the order details
                $existingDetail = OrderDetail::where('order_id', $order->id)
                    ->where('product_id', $favoriteProduct->product_id)
                    ->first();

                if ($existingDetail) {
                    // If already exists, update quantity (assuming you may want to update it)
                    $existingDetail->quantity += 1; // Example: Increment quantity by 1
                    $existingDetail->save();
                } else {
                    // If not exists, create new order detail
                    $orderDetail = new OrderDetail();
                    $orderDetail->order_id = $order->id;
                    $orderDetail->product_id = $favoriteProduct->product_id;
                    $orderDetail->quantity = 1; // Example: Default quantity (can be adjusted as per requirement)
                    $orderDetail->save();
                }
            } else {
                // Handle case where product cannot be added (e.g., out of stock)
                // Optionally, you can skip or log these cases
            }
        } else {
            // Handle case where product is not found in the database
            // Optionally, you can skip or log these cases
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Favorite products added to cart successfully',
        'order_id' => $order->id,
    ], 200);
}
}
