<?php

namespace App\Http\Controllers;

use App\Http\Resources\BoxResource;
use App\Models\Box;
use App\Models\BoxItemList;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoxController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => BoxResource::collection(Box::all()),
        ]);
    }

    public function store()
    {
        $admin_id = auth('api')->id();

        $random_items = Item::inRandomOrder()->limit(3)->get(['id', 'price']);

        $items_price = $random_items->sum('price');

        $box_price = ((10 / 100) * $items_price) + $items_price;

        $payment = new PaymentCotroller('F97SNVD-VVMMBHP-KM6E30M-H4GNSA5');

        $estimate_price = $payment->getEstimatedPrice([
            'amount' => $box_price,
            'currency_from' => 'usd',
            'currency_to' => 'eth',
        ]);

        $box = Box::create([
            'admin_id' => $admin_id,
            'price' => $box_price,
            'estimate_price' => $estimate_price,
        ]);

        foreach ($random_items as $item) {
            BoxItemList::create([
                'box_id' => $box->id,
                'item_id' => $item->id,
            ]);
        }

        return response()->json([
            'success' => 'box created successfully.'
        ], 201);
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Box $box
     * @return void
     */
    public function createBoxPayment(Request $request, Box $box)
    {
        $validate = Validator::make($request->all(), [
            'player_id' => 'required|exists:players,id'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], 422);
        }

        if ($box->paid == 'finished') {
            return response()->json([
                'error' => 'The box has been sold.'
            ], 404);
        }

        $payment = new PaymentCotroller('F97SNVD-VVMMBHP-KM6E30M-H4GNSA5');

        $payment = $payment->createPayment([
            'price_amount' => $box->price,
            'price_currency' => 'usd',
            'pay_currency' => 'eth',
            'order_id' => $box->id,
            'ipn_callback_url' => 'https://lottery-game-v1.herokuapp.com/api/payment-callback',
        ]);

        Payment::create([
            'box_id' => $box->id,
            'player_id' => $request->player_id,
            'payment_id' => $payment['payment_id'],
            'payment_status' => $payment['payment_status'],
            'pay_address' => $payment['pay_address']
        ]);

        return response()->json([
            'data' => [
                'pay_address' => $payment['pay_address']
            ]
        ]);
    }

    public function paymentCallback(Request $request)
    {
        $recived_hmac = $request->header('x-nowpayments-sig');

        $params = $request->all();
        ksort($params);
        $sorted_request_json = json_encode($params, JSON_UNESCAPED_SLASHES);

        $hmac = hash_hmac("sha512", $sorted_request_json, trim('5cinJva4/93fR+ge6XFEA0WVyJrSOPx2'));

        if ($hmac == $recived_hmac) {
            $payment = Payment::where('payment_id', $request->payment_id)->first();

            $payment->update([
                'payment_status' => $request->payment_status,
            ]);

            if ($request->payment_status == 'finished') {
                $box = Box::where('id', $request->order_id)->first();

                if (!$box->player_id) {
                    $box->update([
                        'player_id' => $payment->player_id,
                        'paid' => $request->payment_status,
                        'admin_id' => null
                    ]);
                }
            }
        }
    }

    public function unsoldBoxes()
    {
        return response()->json([
            'data' => BoxResource::collection(Box::where('paid', 'no')->get()),
        ]);
    }

    public function lotteryWinner()
    {
        $winnerBox = Box::whereNotNull('player_id')->inRandomOrder()->first();

        $soldBoxesPrice = Box::where('paid', 'finished')->sum('price');

        $prize = 0.1 * $soldBoxesPrice;

        $winnerPlayer = $winnerBox->player;

        return response()->json([
            'data' => [
                'prize' => $prize . " USD",
                'player_name' => $winnerPlayer->first_name . " " . $winnerPlayer->last_name,
                'player_phone' => $winnerPlayer->mobile_number,
                'player_email' => $winnerPlayer->email,
                'player_gender' => $winnerPlayer->gender,
                'winner_box_price' => $winnerBox->price . " USD",
            ]
        ]);
    }
}
