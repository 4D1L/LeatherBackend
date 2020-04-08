<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['author'];


    /*
    ** Support Ticket Relationship
    ** Type: ONE TO ONE
    */

    /**
     * Get the support ticket that the message belongs to.
     */
    public function ticket()
    {
        return $this->belongsTo('App\SupportTicket', 'ticket_id');
    }

    /**
     * Get the author of a message.
     */
    public function author()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
