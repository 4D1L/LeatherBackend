<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Currency;

class CurrencyController extends Controller
{
    /**
     * Create a new CurrencyController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }

    /**
     * Returns a list supported currencies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $currencies = Currency::all();

        if(!$currencies) {
            return response()->json(['message' => 'No currencies available.'], 404);
        }

        return response()->json($currencies);
    }

    /**
     * Returns information about a specific currency.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($name)
    {
        $currency = Currency::where('name', 'LIKE', $name)->first();

        if(!$currency) {
            return response()->json(['message' => 'Currency not found.'], 404);
        }

        $ret = $currency;
        $ret["price_data"] = $currency->getPriceData($name);
        return response()->json($currency);
    }
}
