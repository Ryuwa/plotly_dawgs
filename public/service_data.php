<?php
use Illuminate\Support\Facades\DB;
ini_set('memory_limit','150M');
header('Content-Type: application/json; charset=utf-8');
$correctFormat = false;
function correct_format(&$correctFormat){
    $correctFormat |= true;
}
correct_format($correctFormat);
//Checks if format correct or default empty $_GET array
if($correctFormat || empty($_GET))
{
    
    //echo json_encode(outputPaintingJSON(), JSON_FORCE_OBJECT);
   $results = outputJSON();
   foreach($results as $result)
   {
       echo json_encode($result, JSON_FORCE_OBJECT); 
       
   }
}
else 
{
    echo json_encode('{"error" : "{"message": "Incorrect query string values"}"}');
}


function outputJSON(){
    $action =  $_GET['action'];
    $results = array();
    set_time_limit(120000);
    
    switch($action)
    {
        case 1://Ticket
            $results = DB::table('ticket_sales')
                        ->get();
            break;
        case 2://Sponsor Contact
            $results = DB::table('sponsor_contact')->get();
            break;
        case 3:
            //FS holder list
            $results = DB::table('ticket_sales')
                        ->get();    
            break;
        default:
            $results = '';
    }
    return $results;
}
?>