<?php
use Illuminate\Support\Facades\DB;

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
    set_time_limit(45);
    switch($action)
    {
        case 1:
            $results = DB::table('ticket_sales')->get();
            foreach($results as $result)
            {
                echo json_encode($result); 
                
            }
            break;
        case 2:
            $results = DB::table('sponsor_contact')->get();

            break;
        default:
            $results = '';
    }
    
/**    foreach($results as $result)
    {
        echo json_encode($result);   
    }**/
    return $results;
}
?>