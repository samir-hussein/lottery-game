<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentCotroller extends Controller
{
    private $token;

    public function __construct(string $your_api_key)
    {
        $this->token = $your_api_key;
    }

    /**
     * Get estimated price
     *
     * @param array $params ['amount' => '25', 'currency_from' => 'usd', 'currency_to' => 'btc']
     * @return string
     */
    public function getEstimatedPrice(array $params)
    {
        return Http::withHeaders([
            'x-api-key' => $this->token,
        ])->get('https://api-sandbox.nowpayments.io/v1/estimate', $params)->json()['estimated_amount'];
    }

    /**
     * Create Payment
     *
     * @param array $params ['price_amount' => '4531.4', 'price_currency' => 'usd', 'pay_currency' => 'eth', 'order_id' => '2132', 'ipn_callback_url' => 'https://example.com']
     * @return array
     */
    public function createPayment(array $params)
    {
        return Http::withHeaders([
            'x-api-key' => $this->token,
            'Content-Type' => 'application/json'
        ])->post('https://api-sandbox.nowpayments.io/v1/payment', $params)->json();
    }

    public function getPaymentStatus(string $payment_id)
    {
        return Http::withHeaders([
            'x-api-key' => $this->token,
        ])->get("https://api-sandbox.nowpayments.io/v1/payment/$payment_id")->json()['payment_status'];
    }
}
