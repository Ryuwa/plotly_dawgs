<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class contract_asset extends Model
{
    //SPECIAL COMPOSITE KEY
    protected $table = 'contract_asset';
    public $primaryKey = 'contractId';
    public $timestamps = false;
}
