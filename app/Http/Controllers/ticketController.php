<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ticketController extends Controller
{
    private $tickets = array();
    
    function __construct(){
        DB::table('ticket_sales');   
    }
}
