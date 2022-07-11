<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\BoxController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentCotroller;
use App\Http\Controllers\PlayerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('admin')->name('admin.')->controller(AdminAuthController::class)->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register')->name('register');
});

Route::apiResource('/item', ItemController::class)->middleware('auth:api')->missing(function () {
    return response()->json([
        'error' => 'not found.'
    ]);
});

Route::post('/player/register', [PlayerController::class, 'register'])->name('player.register');

Route::controller(BoxController::class)->name('box.')->prefix('box')->middleware('auth:api')->group(function () {
    Route::post('/', 'store')->name('store');
    Route::get('/', 'index')->name('index');
});

Route::post('/box/{box}/create-payment', [BoxController::class, 'createBoxPayment'])->name('box.create.payment')->missing(function () {
    return response()->json([
        'error' => 'not found.'
    ]);
});

Route::post('/payment-callback', [BoxController::class, 'paymentCallback'])->name('payment.callback');

Route::get('/test', function () {
    $payment = new PaymentCotroller('F97SNVD-VVMMBHP-KM6E30M-H4GNSA5');

    return response()->json([
        'data' => $payment->getPaymentStatus('4650757014'),
    ]);
});
