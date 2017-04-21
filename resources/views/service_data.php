<?php
header('Content-Type: application/json');


use Illuminate\Support\Facades\DB;


$correctFormat = false;
function correct_format(&$correctFormat){
    $correctFormat |= true;
}
correct_format($correctFormat);
//Checks if format correct or default empty $_GET array
if($correctFormat || empty($_GET))
{
    
    //echo json_encode(outputPaintingJSON(), JSON_FORCE_OBJECT);
   echo json_encode(outputJSON(), JSON_FORCE_OBJECT);
 
}
else 
{
    echo json_encode('{"error" : "{"message": "Incorrect query string values"}"}');
}


function outputJSON(){
    $action =  $_GET['action'];
    if(!empty($_GET['year']))
        $year = $_GET['year'];
    $results = array();
    set_time_limit(45);
    switch($action)
    {
        case 1:
            //non-renewal STH YEAR WILL HAVE TO BE CHANGED SOON
            $results = DB::select(DB::raw("select distinct a.customerId, phone, firstName, email, city, cellPhone, lastName, a.year as aYear, b.year as bYear, a.product as prev,b.product as latest 
                                            from (select customerId, year, product from ticket_sales
                                            	where year = 2016
                                            	and product = 'Season Ticket'
                                            ) a
                                            left join ( select customerId, year, product from ticket_sales
                                            	where year = 2017
                                            ) b
                                            on a.customerId = b.customerId
                                            and b.product != 'Season Ticket'
                                            join customer_master c on a.customerId = c.customerId"));
            break;
        case 2://Ticket Available vs Days Till Game
            $max_seating = DB::table('SeatingCapacityDawgs')
                            ->select(DB::raw("sum(capacity) as max_capacity"))
                            ->get()[0]->max_capacity;
                            
            $results = DB::select(DB::raw("select e.eventId, e.eventDate, datediff(day, getdate(), e.eventDate) as days_till_event, count(distinct t.ticketId) as num_sold, $max_seating-count(distinct t.ticketId) as seats_left
                                           from ticket_sales t join event_master e
                                           on t.eventId = e.eventId
                                           group by e.eventId, e.eventDate
                                           order by e.eventId"));
            
            // foreach($results as $item){
            //     $max_seating -= $item->num_sold;
            //     $item->capacity_left = $max_seating;
            // }
            break;
        case 3:
            //sankey migration
            $results = DB::select(DB::raw("select distinct a.customerId, a.firstName, a.LastName, a.type16, b.type17
                                           from
                                           (select distinct t.customerId, firstName, lastName, year, product as type16
                                           from ticket_sales t join customer_master c
                                           on t.customerId = c.customerId
                                           where year = 2016
                                           and product = 'Season Ticket') a
                                           left join
                                           (select distinct t.customerId, firstName, lastName, year, product as type17
                                           from ticket_sales t join customer_master c
                                           on t.customerId = c.customerId
                                           where year = 2017) b
                                           on a.customerId = b.customerId"));
            break;
        case 4:
            //heat map by revenue
            $results = DB::table('ticket_sales')
                        ->select('section', 'row', DB::raw('sum(cost) as revenue'))
                        ->groupBy('section','row')
                        ->get();
            break;
        case 5:
            //heatmap by ATP
            $non_playoffs = DB::table('event_master')
                            ->select(DB::raw('max(eventId) as max_event'))
                            ->where('attended', '>', 0)
                            ->get()[0]->max_event;
            
            $results = DB::select(DB::raw("select section, row, avg(cost) as ATP from ticket_sales
                                           where eventId <= $non_playoffs
                                           group by section, row"));
            break;
        case 6:
            //Heatmap by STR
            $num_events = (Int) DB::table('event_master')
                        ->select(DB::raw('count(*) as num_events'))
                        ->where('year', '<=', 2016)
                        ->get()[0]->num_events;
                        
            
            $non_playoffs = DB::table('event_master')
                            ->select(DB::raw('max(eventId) as max_event'))
                            ->where('attended', '>', 0)
                            ->get()[0]->max_event;
                           
            $results = DB::select(DB::raw("select a.section, a.row, a.num_tickets, b.capacity * $num_events as season_capacity from
                                            (
                                            	select section, row, count(distinct ticketId) as num_tickets from ticket_sales	
                                            	where orderDate < '2016-12-19'
                                            	and eventId <= $non_playoffs
                                            	group by section, row
                                            ) a
                                            join 
                                            (
                                            	select * from SeatingCapacityDawgs
                                            )b
                                            on a.section = b.section
                                            and a.row = b.row"));
            //var_dump($results); 
            break;
        case 7:
            //cumulative revenue overlay WILL NEED TO PASS IN YEAR OR DO A LOOP
            $max_order_date = DB::table('ticket_sales')
                                ->select(DB::raw("max(orderDate) as max_order_date"))
                                ->whereYear('orderDate','=', $_GET['year'])
                                ->get()[0]->max_order_date;

            $results = DB::table('ticket_sales')
                        ->select('orderDate', DB::raw('sum(cost) as revenue'))
                        ->where('orderDate', '<', $max_order_date)
                        ->groupBy('orderDate')
                        ->orderBy('orderDate')->get();
            break;
        case 8:
            //Revenue Progress to Goal
            $results = DB::table('ticket_sales')
                        ->select(DB::raw("sum(cost) as revenue_progress"))
                        ->where('year', '=', $year)
                        ->get();
            break;
        case 9:
            //ATP Progress to Goal
            $results = DB::table('ticket_sales')
                        ->select(DB::raw("avg(cost) as atp_progress"))
                        ->where('year', '=' ,$year)
                        ->get();
            break;
        case 10:
            //FS Renewal Progress to Goal STH
            $renewal_2017 = DB::table('ticket_sales')
                            ->select(DB::raw("count(distinct customerId) as renewal"))
                            ->where('year', '=', 2017)
                            ->where('product', '=', "Season Ticket")
                            ->get()[0]->renewal;
            
            $renewal_2016 = DB::table('ticket_sales')
                            ->select(DB::raw("(count(distinct customerId)) as renewal"))
                            ->where('year', '=', 2016)
                            ->where('product', '=', 'Season Ticket')
                            ->get()[0]->renewal;
            
            $results = new \stdClass();
            $results->renewal_2016 = $renewal_2016;
            $results->renewal_2017 = $renewal_2017;
            break;
        case 11:
            //Pacing to Goal By Product
            
            //FS Tickets
            $max_order_date = DB::table('ticket_sales')
                                ->select(DB::raw('max(orderDate) as max_order_date'))
                                ->whereYear('orderDate', '=', 2016)
                                ->get()[0]->max_order_date;
            
            $results['fs'] = DB::table('ticket_sales')
                            ->select(DB::raw('sum(cost) as fs_revenue'))
                            ->where('product', '=', 'Season Ticket')
                            ->where('orderDate', '<', $max_order_date)
                            ->get();
                        
            $results['berm'] = DB::table('ticket_sales')
                                ->select(DB::raw('sum(cost) as berm_revenue'))
                                ->where('section', '=', 'Berm')
                                ->where('orderDate', '<', $max_order_date)
                                ->get();
                                
            $results['res'] = DB::table('ticket_sales')
                                ->select(DB::raw('sum(cost) as reserved_revenue'))
                                ->where('orderDate', '<', $max_order_date)
                                ->where('product', '=', 'Ticket')
                                ->where('section', '!=', 'Berm')
                                ->get();            
            break;
        default:
            $results = '';
    }
    
    return $results;
}
?>
