<?php

namespace App\Helpers;

use \BlockCypher\Auth\SimpleTokenCredential;
use \BlockCypher\Rest\ApiContext;

class BlockcypherAPI {
    
    /**
     * This is a singleton for to simplify interaction with the BlockcypherAPI.
     */

    // Variable to hold instance of ApiContext.
    private $instance;

    // Connect to Blockcypher and then store it in the instance variable.
    public function __construct()
    {
        $this->instance = ApiContext::create(
            'test', 'bcy', 'v1',
            new SimpleTokenCredential(config('blockcypher.token')),
            array('log.LogEnabled' => false, 'log.FileName' => 'BlockCypher.log', 'log.LogLevel' => 'FINE')
        );
    }

    // Static function to get the Blockcypher ApiContext object.
    public static function getInstance()
    {
        return app(BlockcypherAPI::class)->instance;
    }
}