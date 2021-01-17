<?php

namespace App\Http\Controllers;

use App\Models\Paystack;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class PaystackController extends Controller
{
    private $client;
    /**
     * Create a new client instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.paystack.co/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('paystack.secret')
            ]
        ]);
    }

    public function initialize(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required'
            ]);

            $response = $this->client->post('transaction/initialize', [
                'json' => [
                    "amount" => $request->amount * 100,
                    "email" => $request->user()->email
                ]
            ]);

            return $response;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function verify(Request $request)
    {
        try {
            $response = $this->client->get('transaction/verify/' . $request->reference);

            $response = json_decode($response->getBody(), true);

            if ($response['status']) {
                $user = User::whereEmail($response['data']['customer']['email'])->first();

                if($user->paystack){
                    $user->paystack->delete();
                }

                Paystack::create([
                    'user_id' => $user->id,
                    'authorization' => $response['data']['authorization']['authorization_code']
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'authorization code added'
                ], 200);;
            }
            return response()->json([
                'status' => 'failed',
                'message' => 'verification failed'
            ], 400);;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function chargeAuthorization($code, $amount, $email)
    {
        try {
            $response = $this->client->post('transaction/charge_authorization/', [
                'json' => [
                    "amount" => $amount * 100,
                    "email" => $email,
                    "authorization_code" => $code
                ]
            ]);

            $response = json_decode($response->getBody(), true);
            return $response['message'];
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function charge(Request $request)
    {
        $code = $request->user()->paystack->authorization;
        $amount = $request->user()->active_loan()->amount;
        $res = $this->chargeAuthorization($code, $amount, $request->user()->email);

        return $res;
    }
}
