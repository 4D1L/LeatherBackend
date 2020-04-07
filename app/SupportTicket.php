<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['messages', 'author'];

    /*
    ** Support ticket Relationship
    */

    /**
     * Get the author of a support ticket.
     */
    public function author()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /*
    ** Support Ticket Message Relationship
    */

    /**
     * Get the support tickets created by the user.
     */
    public function messages()
    {
        return $this->hasMany('App\SupportTicketMessage', 'ticket_id');
    }
}
