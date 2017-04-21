<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class sponsor_master extends Model
{
    protected $table = 'sponsor_master';
    public $primaryKey = 'sponsorId';
    public $timestamps = false;
}
