<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket_Sale extends Model
{
    protected $table = 'ticket_sales';
    public $primaryKey = 'ticketId';
    public $timestamps = false;
}
