<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Wallet;
use App\Currency;
use App\Helpers\BlockcypherAPI;

// Include the SDK modules you need.
use \BlockCypher\Client\AddressClient;

class WalletController extends Controller
{
    /**
     * Create a new WalletController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Create a new wallet and link it to the user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        // Fetch bitcoin.
        $currency = Currency::where('name', 'LIKE', 'Bitcoin')->first();

        if(!$currency) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The currency does not exist."
                ]
            ];
            return response()->json($response, 404);
        }

        // Use the SDK to generate a new address on the test network.
        $addressClient = new AddressClient(BlockcypherAPI::getInstance());
        $addressKeyChain = $addressClient->generateAddress();

        // Store the wallet information in our system.
        $wallet = new Wallet();
        $wallet->currency_id = $currency->id;
        $wallet->user_id = Auth::user()->id;
        $wallet->publicKey = $addressKeyChain->public;
        $wallet->privateKey = $addressKeyChain->private;
        $wallet->address = $addressKeyChain->address;
        $wallet->save();

        // Return information about the newly created wallet.
        $response = [
            "success" => true,
            "response" => $wallet
        ];

        return response()->json($response);
    }
}
