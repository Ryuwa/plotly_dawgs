<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class event_master extends Model
{
    protected $table = 'event_master';
    public $primaryKey = 'eventId';
    public $timestamps = false;
}
