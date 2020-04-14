<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['user', 'currency'];

    /**
     * Get the owner of a wallet
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * Get the owner of a wallet
     */
    public function currency()
    {
        return $this->belongsTo('App\Currency', 'currency_id');
    }
}
