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
        $bitcoin = new Currency();
        $bitcoin->name = "Bitcoin";
        $bitcoin->short = "BTC";
        $bitcoin->transactions_supported = true;
        $bitcoin->save();
    }
}
