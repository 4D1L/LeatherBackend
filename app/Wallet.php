<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{

    use SoftDeletes;
    
    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['currency'];

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
