<?php

use App\Http\Controllers\Auth;
use App\Http\Controllers\FavoriteProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\PredictController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\test;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::group(['middleware' => 'auth:sanctum'], function () {

//     // Route::post('/logout', [AuthenticationController::class, 'logout']);

// });

Route::group(['middleware' => ['auth:sanctum', 'responsible']], function () {
    // Route::post('product/create',[ProductController::class,'store']);

});


// Route::group(['middleware' => ['auth:sanctum', 'customer']], function () {



// });

Route::group(['middleware' => 'auth:sanctum'], function () {

        Route::post('order/create',[OrderController::class,'store']);
        Route::get('user/profile',[UserController::class,'profile']);
        Route::get('order/cart/',[OrderController::class,'getCart']);
        Route::delete('cart',[OrderDetailController::class,'resetCart']);
        Route::post('favorite/cart',[FavoriteProductController::class,'addFavoriteProductsToCart']);
        Route::post('user/favorite',[FavoriteProductController::class,'Favorite']);
        Route::get('favorite/user',[FavoriteProductController::class,'getAllFavoritesForUser']);
        Route::delete('favorite',[FavoriteProductController::class,'deleteAllFavoritesForUser']);


});


Route::post('user/createuser',[Auth::class,'register'])->withoutMiddleware(['auth:sanctum']);
Route::post('user/login',[Auth::class,'login'])->withoutMiddleware(['auth:sanctum']);


Route::post('predict',[PredictController::class,'predict']);


Route::get('user/allusers',[UserController::class,'index']);
Route::put('user/updateuser/{id}',[UserController::class,'update']);
Route::delete('user/deleteuser/{email}',[UserController::class,'destroy']);
Route::get('user/{email}',[UserController::class,'showUsingEmail']);
// Route::get('user/update',[UserController::class,'updateToken']);
// Route::get('user/profile',[UserController::class,'profile']);
Route::get('user/order/{userId}',[UserController::class,'orders']);


Route::post('product/create',[ProductController::class,'store']);
Route::get('product/{id}', [ProductController::class, 'show']);
Route::get('products',[ProductController::class,'index']);
Route::put('product/updateproduct/{id}',[ProductController::class,'update']);
Route::delete('product/deleteproduct/{id}',[ProductController::class,'destroy']);
Route::put('product/updaterequestcount/increase/{orderId}',[ProductController::class,'updateRequestCount_increase']);
Route::put('product/updaterequestcount/decrease/{orderId}',[ProductController::class,'updateRequestCount_decrease']);
Route::put('product/updatequantity/decrease/{orderId}',[ProductController::class,'updateRemainingQuantity_decrease']);
Route::put('product/updatequantity/increase/{orderId}',[ProductController::class,'updateRemainingQuantity_increase']);
Route::get('product',[ProductController::class,'bestSalary']);
Route::get('category',[ProductController::class,'getCategories']);
Route::get('brand',[ProductController::class,'manufacturer_name']);
Route::get('/latestproducts', [ProductController::class, 'showLatestProducts']);


// Route::post('order/c reate',[OrderController::class,'store']);
Route::get('order/{id}', [OrderController::class, 'show']);
Route::get('orders',[OrderController::class,'index']);
Route::put('order/updateorder/{id}',[OrderController::class,'update']);
Route::put('order/confirm/{orderId}',[OrderController::class,'changeOrderStatusToConfirm']);
Route::put('order/statusone/{orderId}',[OrderController::class,'changeOrderStatusToOne']);
Route::put('order/statustwo/{orderId}',[OrderController::class,'changeOrderStatusToTwo']);
Route::put('order/statusthree/{orderId}',[OrderController::class,'changeOrderStatusToThree']);
Route::delete('order/deleteorder/{id}',[OrderController::class,'destroy']);
// Route::get('order/cart/{userid}',[OrderController::class,'getCart']);


// Route::post('user/favorite',[FavoriteProductController::class,'Favorite']);
// Route::get('favorite/user/{userID}',[FavoriteProductController::class,'getAllFavoritesForUser']);
// Route::delete('favorite/{userID}',[FavoriteProductController::class,'deleteAllFavoritesForUser']);
// Route::post('favorite/cart',[FavoriteProductController::class,'addFavoriteProductsToCart']);


Route::post('orderdetails/create',[OrderDetailController::class,'store']);
// Route::put('orderdetails/updatedetails/{id}',[OrderDetailController::class,'update']);
Route::put('orderdetails/update/{orderId}/{productId}',[OrderDetailController::class,'update']);
Route::get('orderdetails/all/{orderId}',[OrderDetailController::class,'getOrderDetails']);
Route::delete('order/{order_id}/product/{product_id}',[OrderDetailController::class,'deleteProductFromOrder']);
// Route::delete('cart/{user_id}/',[OrderDetailController::class,'resestCart']);


