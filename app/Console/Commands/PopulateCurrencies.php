<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Currency;

class PopulateCurrencies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:currencies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This commands populates the currency table with supported currencies.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //

        $btcCheck = Currency::where('short', 'LIKE', 'BTC')->count();
        if($btcCheck == 0)
        {
            $bitcoin = new Currency();
            $bitcoin->name = "Bitcoin";
            $bitcoin->short = "BTC";
            $bitcoin->transactions_supported = true;
            $bitcoin->save();            
        }

        $ethCheck = Currency::where('short', 'LIKE', 'ETH')->count();
        if($ethCheck == 0)
        {
            $ethereum = new Currency();
            $ethereum->name = "Ethereum";
            $ethereum->short = "ETH";
            $ethereum->transactions_supported = false;
            $ethereum->save();            
        }

        $ltcCheck = Currency::where('short', 'LIKE', 'LTC')->count();
        if($ltcCheck == 0)
        {
            $litecoin = new Currency();
            $litecoin->name = "Litecoin";
            $litecoin->short = "LTC";
            $litecoin->transactions_supported = false;
            $litecoin->save();            
        }

    }
}
