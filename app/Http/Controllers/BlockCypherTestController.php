<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\BlockcypherAPI;

// Include the SDK modules you need.
use \BlockCypher\Client\BlockchainClient;

class BlockCypherTestController extends Controller
{
    /**
     * This class shows how you could use the Blockcyper SDK helper.
     * Examples are inspired by https://github.com/blockcypher/php-client/wiki
     */

    
    /**
     * Gets information about the bitcoin blockchain.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Instantiate the module using our wrapper.
        $blockchainClient = new BlockchainClient(BlockcypherAPI::getInstance());

        // Interact with the API using the SDK.
        $blockchain = $blockchainClient->get('BTC.main');

        // Return the result.
        return response($blockchain);
    }
}
