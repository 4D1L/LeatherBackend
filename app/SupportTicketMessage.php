<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SupportTicketMessage extends Model
{
    /*
    ** Support Ticket Relationship
    ** Type: ONE TO ONE
    */

    /**
     * Get the author of a support ticket.
     */
    public function ticket()
    {
        return $this->belongsTo('App\SupportTicketMessage', 'ticket_id');
    }
}
