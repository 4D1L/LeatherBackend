<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'short', 'transactions_supported'
    ];

    /**
     * Gets currency price data from coingecko.
     *
     * @var array
     */
    public function getPriceData($name)
    {
        $api_url = sprintf('https://api.coingecko.com/api/v3/coins/markets?vs_currency=gbp&ids=%s&order=market_cap_desc&per_page=100&page=1&sparkline=false&price_change_percentage=24h', $name);
        $client = new \GuzzleHttp\Client();
        $request = $client->get($api_url);
        $response = $request->getBody()->getContents();
        $array = json_decode($response);

        $ret["current_price"] = $array[0]->current_price;
        $ret["market_cap"] = $array[0]->market_cap;
        $ret["total_volume"] = $array[0]->total_volume;
        $ret["high_24h"] = $array[0]->high_24h;
        $ret["low_24h"] = $array[0]->low_24h;
        $ret["price_change_24h"] = $array[0]->price_change_24h;
        $ret["price_change_percentage_24h"] = $array[0]->price_change_percentage_24h;
        return $ret;
    }
}
