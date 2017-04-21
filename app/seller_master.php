<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class seller_master extends Model
{
    protected $table = 'seller_master';
    public $primaryKey = 'sellerId';
    public $timestamps = false;
}
