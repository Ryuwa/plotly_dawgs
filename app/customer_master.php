<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class customer_master extends Model
{
    protected $table = 'customer_master';
    public $primaryKey = 'customerId';
    public $timestamps = false;
}
