<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class asset_master extends Model
{
    protected $table = 'asset_master';
    public $primaryKey = 'assetId';
    public $timestamps = false;
}
