<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
    public function create(Request $request)
    {

        // Validate the submission. Wallets are different for currencies.
        $validator = Validator::make($request->all(), [
            'currency' => 'required',
        ]);

        // If validation fails, return an error message.
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The form is not complete."
                ]
            ];
            return response()->json($response, 400);
        }

        // Fetch currency.
        $currency = Currency::where('name', 'LIKE', $request->currency)->first();

        if(!$currency || $currency->transactions_supported == false) {
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
        $wallet->user_id = Auth::user()->id;
        $wallet->publicKey = $addressKeyChain->public;
        $wallet->privateKey = $addressKeyChain->private;
        $wallet->address = $addressKeyChain->address;
        
        $currency->wallets()->save($wallet);

        // Return information about the newly created wallet.
        $response = [
            "success" => true,
            "response" => $wallet
        ];

        return response()->json($response);
    }

    /**
     * Allow a user to delete a wallet.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        // Get the instance of wallet by its ID.
        $wallet = Wallet::where('id', $id)->get();

        // If it does not exist, return an error message.
        if(!$wallet) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The wallet does not exist."
                ]
            ];
            return response()->json($response, 404);
        }

        // If the logged in user does not own the wallet, return an error message.
        if($wallet->user->id === Auth::user()->id) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No permissions to make changes to this wallet."
                ]
            ];
            return response()->json($response, 403);
        }
    
        // Otherwise, destroy the instance.
        $wallet->destroy();

        // Return a message.
        $response = [
            'success' => true,
            'response' => [
                "message" => "The wallet was deleted."
            ]
        ];

        return response()->json($response);
    }

    /**
     *  Returns a list of user wallets.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($currencyName = null)
    {
        // If a currency name is not provided, return an array of all the wallets associated with the user.
        if($currencyName == null)
        {
            $wallets = Auth::user()->wallets()->get();
            $response = [
                'success' => true,
                'response' => $wallets
            ];

            return response()->json($response);
        }

        // Otherwise, find the instance of the currency being requested.
        $currency = Currency::where('name', 'LIKE', $currencyName)->first();

        // If it does not exist, return an error message.
        if(!$currency) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The currency does not exist."
                ]
            ];
            return response()->json($response, 404);
        }

        // Fetch wallets which are a part of a currency's block chain and are owned by the user.
        $wallets = Auth::user()->wallets()->where('currency_id', $currency->id)->get();

        // Return the wallets.
        $response = [
            'success' => true,
            'response' => $wallets
        ];

        return response()->json($response);

    }
}
