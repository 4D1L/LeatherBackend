<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\BlockcypherAPI;

// Include the SDK modules you need.
use \BlockCypher\Api\TX;
use \BlockCypher\Client\TXClient;
use \BlockCypher\Client\MicroTXClient;

class TransactionController extends Controller
{
    /**
     * Create a new TransactionController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['createUNSAFE']]);
    }

    public function createUNSAFE(Request $request)
    {
        // Validate the submission. Tickets need a ticket and an initial message.
        $validator = Validator::make($request->all(), [
            'sender_address' => 'required|max:255',
            'sender_private_key' => 'required',
            'recipient_address' => 'required',
            'amount' => 'required|numeric'
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

        $value = $request->amount * 100000000;

        // Create a new transactions object.
        $tx = new TX();

        $input = \BlockCypher\Builder\TXInputBuilder::aTXInput()
            ->addAddress($request->sender_address)
            ->build();

        $output = \BlockCypher\Builder\TXOutputBuilder::aTXOutput()
            ->addAddress($request->recipient_address)
            ->withValue($value)
            ->build();

        $tx = \BlockCypher\Builder\TXBuilder::aTX()
            ->addTXInput($input)
            ->addTXOutput($output)
            ->build();

        $txClient = new TXClient(BlockcypherAPI::getInstance());

        try {
            $txSkeleton = $txClient->create($tx);
        } catch (Exception $ex) {
            $response = [
                'success' => false,
                'response' => $ex
            ];

            return response()->json($response);
        }

        //$privateKeys = array("d103370737331e166b0531f6b9deeb06385201de12e3876f340fc7f7a85205df");

        try {
            $txSkeleton = $txClient->sign($txSkeleton, $request->sender_private_key);
            $txSkeleton = $txClient->send($txSkeleton);
        } catch (Exception $ex) {
            $response = [
                'success' => false,
                'response' => $ex
            ];

            return response()->json($response);
        }
            
        $response = [
            'success' => true,
            'response' => $txSkeleton
        ];

        return response()->json($response);
    }

    public function createMicroUNSAFE(Request $request)
    {
        // Validate the submission. Tickets need a ticket and an initial message.
        $validator = Validator::make($request->all(), [
            'sender_private_key' => 'required',
            'recipient_address' => 'required',
            'amount' => 'required|numeric'
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

        $value = $request->amount * 100000000;
        if(7000 > $value || $value > 4000000) {
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
            $request->sender_private_key, // private key
            $request->recipient_address, // to address
            $request->amount // value (satoshis)
        );

        return response($microTX);
    }
}
