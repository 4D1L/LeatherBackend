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
use \BlockCypher\Client\MicroTXClient;

class WalletController extends Controller
{
    /**
     * Create a new WalletController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['createUNSAFE', 'getDetailsUNSAFE', 'buyUNSAFE']]);
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

    /**
     *  Create a wallet and not store in the system.
     *  It is unsafe because there are no considerations for the API use limit.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUNSAFE()
    {
        // Use the SDK to generate a new address on the test network.
        $addressClient = new AddressClient(BlockcypherAPI::getInstance());
        $addressKeyChain = $addressClient->generateAddress();

        return response()->json([
            "public" => $addressKeyChain->public,
            "private" => $addressKeyChain->private,
            "address" => $addressKeyChain->address,
            "wif" => $addressKeyChain->wif
        ]);
    }

    /**
     * Gets details about a wallet and its transactions.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailsUNSAFE($address)
    {
        $addressClient = new AddressClient(BlockcypherAPI::getInstance());
        try{
            $details = $addressClient->getFullAddress($address);
        } catch(\BlockCypher\Exception\BlockCypherConnectionException $ex) {
            $response = [
                'success' => false,
                'response' => "Address does not exist."
            ];
            return response()->json($response);
        }
        

        $response = [
            'success' => true,
            'response' => json_decode($details)
        ];

        return response()->json($response);
    }

    /**
     * Buy cryptocurrency (Off the system)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyUNSAFE(Request $request)
    {
        // Validate the submission. Tickets need a ticket and an initial message.
        $validator = Validator::make($request->all(), [
            'recipient_address' => 'required',
            'amount' => 'required|numeric'
        ]);

        // If validation fails, return an error message.
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The form is not complete.",
                    "values" => [
                        'recipient_address' => $request->recipient_address,
                        'amount' => $request->amount
                    ]
                ]
            ];
            return response()->json($response, 400);
        }

        $value = $request->amount * 100000000;
        if(10000 > $value || $value > 4000000) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The system only allows transactions up to $20 (4,000,000 satoshis)."
                ]
            ];
            return response()->json($response);
        }

        $microTXClient = new MicroTXClient(BlockcypherAPI::getinstance());
        $microTX = $microTXClient->sendWithPrivateKey(
            config('blockcypher.system_private_key'), // private key
            $request->recipient_address, // to address
            $value // value (satoshis)
        );

        $response = [
            'success' => true,
            'response' => json_decode($microTX)
        ];
        return response()->json($response);
    }
}
