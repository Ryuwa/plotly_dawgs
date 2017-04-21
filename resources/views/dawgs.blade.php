<!DOCTYPE html>
<html lang="en-us">

<head>
    <meta charset="utf-8">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <link href="{{ secure_asset('c3.css') }}" rel="stylesheet" type="text/css">
    <script src="https://d3js.org/d3.v4.min.js"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://npmcdn.com/tether@1.2.4/dist/js/tether.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
</head>

<body >
    <div id = 'progress'>
        <div id = 'revenue_progress'></div>
        <div id = 'atp_progress'></div>
        <div id = 'renewal_progress'></div>
    </div>
    <div id = 'ticket_holders'>
        <div id = 'ticket_holder_migration'></div>
        <div id = 'nonrenew_sth_list'>
            <div class='container'>
                <div class='row'>
                    <div class="panel panel-default" id='panel_list'>
                        <div class="panel-heading">
                          <h4>
                            Non-Renewed List of 2016 STH
                          </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id = 'migration_sankey'></div>
    </div>
    <div id = 'ticket_sales'>
        <div id = 'str_heatmap'></div>
        <div id = 'ticket_vs_day_available'></div>
        <div id = 'cumulative_rev'></div>
        <div id = 'pacing_product'></div>
        
    </div>
</body>  
<script>
var web_services = "https://plotly-test-ryuwa.c9users.io/service_data?action=";
var year = "&year=";

function migration_sankey(){
    var ajax_options = {
        url: web_services.concat(3),
        type: 'GET',
        dataType: 'json',
        success: function(data){
            var fs_origin_count = 0;
            var non_buy_count = 0;
            var single_count = 0;
            var fs_dest_count = 0;
            var total = 0;
            
            $.each(data, function(index, value) {
                if(value.type16)
                    fs_origin_count++;
                if(value.type17 == 'Season Ticket')
                    fs_dest_count++;
                else if(value.type17 == 'Ticket')
                    single_count++;
                else if(!value.type17)
                    non_buy_count++;
                total++;
            });
            
            var rows = Array();
            
            rows.push(['2016 STH', '2017 STH', parseFloat(((fs_dest_count/fs_origin_count)*100).toFixed(2))]);
            rows.push(['2016 STH', '2017 Singles', 0.01]); //currently suppose to be 0
            rows.push(['2016 STH', 'Non Buyer', parseFloat(((non_buy_count/fs_origin_count)*100).toFixed(2))]);
            
            var draw_sankey = function (){
                var dataz = new google.visualization.DataTable();
                dataz.addColumn('string', 'From');
                dataz.addColumn('string', 'To');
                dataz.addColumn('number', 'Percent');
                dataz.addRows(rows);
                // Sets chart options.
                var options = {
                  width: 600,
                };
        
                // Instantiates and draws our chart, passing in some options.
                var chart = new google.visualization.Sankey(document.getElementById('migration_sankey'));
                chart.draw(dataz, options);  
            };
            
            google.charts.load('current', {'packages':['sankey']});
            google.charts.setOnLoadCallback(draw_sankey);
        },
        error: function(){
            
        }
    };
    $.ajax(ajax_options);
}

function ticket_available_game(){
    var ajax_options = {
        url: web_services.concat(2),
        type: 'GET',
        dataType: 'json',  
        
        success: function(data){
            var traces = Array();
            var x = Array();
            var y = Array();
            var z = Array();
            
            //to be worked on
            var make_trace = function (x, y, text) {
                return {
                    x: x,
                    y: y,
                    text: text,
                    hoverinfo: 'text',
                    mode: 'markers'
                }
            }; 
            
            $.each(data, function(index, value){
                x.push(value.days_till_event);
                y.push(value.seats_left);
                z.push("Event ID: "
                    + value.eventId
                    + '<br>' + "Days left: " + value.days_till_event
                    + 'Event Date: ' + new Date(value.eventDate).toDateString());
                
                traces.push(make_trace(x, y, z));
            });
            
            
            
            var ticket_to_days = {
                x: x,
                y: y,
                text: z,
                hoverinfo: 'text',
                mode: 'markers',
                name: 'Tickets to Days Until Game',
                marker: {
                    color: 'rgba(10, 50, 230, 1)',
                    width: 1
                }
            };
            var data = [ticket_to_days];
            var layout = {
                title: 'Ticket Available vs Days Till Game'
            };
            Plotly.newPlot('ticket_vs_day_available', data, layout, {displaylogo: false});
        },
        error: function(){
            console.log('Ticket vs Days Failed to Generate');
        }
    };
    $.ajax(ajax_options);
}

function renewal_progression(){
    var ajax_options = {
        url: web_services.concat(10),
        type: 'GET',
        dataType: 'json',
        
        success: function(data){
            var total = 1.00;
            var progression = {
                x: [parseFloat(data.renewal_2017)/parseFloat(data.renewal_2016)],
                y: ['Renewal'],
                type: 'bar',
                orientation: 'h',
                name: 'Current',
                marker: {
                    color: 'rgba(10, 50, 230, 1)',
                    width: 1
                }
            };
            total -= progression.x[0];
            
            var previous = {
                x: [total],
                y: ['Renewal'],
                type: 'bar',
                orientation: 'h',
                name: 'Last Year STH',
                marker: {
                    color: 'rgba(211, 93, 9, 1)',
                    width: 1
                }
            };
                
            var data = [progression, previous];
            var layout = {
                title: 'STH Renewal Progression',
                barmode: 'stack',
                autosize: false,
                margin: {
                  l: 50,
                  r: 50
                }
            };
            Plotly.newPlot('renewal_progress', data, layout, {displaylogo: false});
        },
        
        error: function(){
            console.log('Renewal Progress Failed to Generate');
        }
    }
    $.ajax(ajax_options);
}

function pacing_by_product(){
    var products = ["Full Season", "Berm Seating", "Individual Seating"];
    var ajax_options = {
        url: web_services.concat(11),
        type: 'GET',
        dataType: 'json',
        
        success: function(data){
            var fs_text = "Type: Full Season" + "<br>" + "Current Revenue: $" + data.fs[0].fs_revenue;
            var bs_text = "Type: Berm Seating" + "<br>" + "Current Revenue: $" + data.berm[0].berm_revenue;
            var rs_text = "Type: Reserved Seating" + "<br>" + "Current Revenue: $" + data.res[0].reserved_revenue;
            
            
            var fs = {
                x: [data.fs[0].fs_revenue],
                y: ["Full Season"],
                text: [fs_text],
                hoverinfo: 'text',
                type: 'bar',
                orientation: 'h',
                name: "Full Season",
                marker: {
                    color: 'rgb(12, 51, 178)',
                    width: 1
                }
            };
            var bs = {
                x: [data.berm[0].berm_revenue],
                y: ["Berm Seating"],
                text: [bs_text],
                hoverinfo: 'text',
                type: 'bar',
                orientation: 'h',
                name: "Berm Seating",
                marker: {
                    color: 'rgb(229, 110, 0)',
                    width: 1
                }
            };
            var rs = {
                x: [data.res[0].reserved_revenue],
                y: ["Individual Seating"],
                text: [rs_text],
                hoverinfo: 'text',
                yangle: '90',
                type: 'bar',
                orientation: 'h',
                name: "Individual Seating",
                marker: {
                    color: 'rgb(45, 178, 12)',
                    width: 1
                }
            };
            var data = [fs, bs, rs];
            var layout = {
                title: 'Pacing to Goal by Product',
                barmode: 'stack'
            };
            Plotly.newPlot('pacing_product', data, layout, {displaylogo: false});
        },
        
        error: function(){
            console.log('Pacing to Goal by Product Failed to Generate');
        }
    };
    $.ajax(ajax_options);
}

//will require a year entry
function ticket_revenue_progress(){
    var ajax_options = {
        url: web_services.concat('8').concat(year.concat(2016)),
        type: 'GET',
        dataType: 'json',
        
        success: function(data){
            var goal = 500000;
            var object_revenue = {
                x: [data[0].revenue_progress],
                y: ['Revenue'],
                type: 'bar',
                orientation: 'h',
                name: 'Current',
                marker: {
                    color: 'rgba(10, 50, 230, 1)',
                    width: 1
                }
            };
            goal -= object_revenue.x[0];
            var text = "$" + goal + " till goal";
            console.log(text);
            var object_goal = {
                x: [goal],
                y: ['Revenue'],
                type: 'bar',
                orientation: 'h',
                text: text,
                name: 'Left Until Goal',
                marker: {
                    color: 'rgba(211, 93, 9, 1)',
                    width: 1
                }
            };
            
            var data = [object_revenue, object_goal];
            var layout = {
                title: 'Ticket Revenue Progress to Goal',
                barmode: 'stack'
            };
            
            Plotly.newPlot('revenue_progress', data, layout, {displaylogo: false});
        },
        
        error: function(){
            console.log('Ticket Revenue Progress to Goal Failed to Generate');
        }
    };
    $.ajax(ajax_options);
}



function ticket_average_progress(){
    var ajax_options = {
        url: web_services.concat('9').concat(year.concat(2016)),
        type: 'GET',
        dataType: 'json',
        
        success: function(data){
            var goal_price = 8; //arbitrary
            
            var object_progress = {
                x: [data[0].atp_progress],
                y: ['Average Ticket Price'],
                type: 'bar',
                orientation: 'h',
                name: 'Current',
                marker : {
                    color: 'rgba(10, 50, 230, 1)',
                    width: 1
                }
            };
            
            goal_price -= object_progress.x[0];
            
            var object_goal = {
                x: [goal_price],
                y: ['Average Ticket Price'],
                type: 'bar',
                orientation: 'h',
                name: 'Left Until Goal',
                marker : {
                    color: 'rgba(211, 93, 9, 1)',
                    width: 1
                }
            };
            
            var data = [object_progress, object_goal];
            
            var layout = {
                title: 'Average Ticket Price Progress to Goal',
                barmode: 'stack'
            };
            
            Plotly.newPlot('atp_progress', data, layout, {displaylogo: false});
        },
        
        error: function(){
            console.log('Average Ticket Price Progress to Goal Failed to Generate');
        }
    };
    
    $.ajax(ajax_options);
}

function non_renewed_list(){
    var ajax_options = {
         url: web_services.concat(1),
         type: 'GET',
         dataType: 'json',
         
         success: function(data){
             var items = Array();
             var table_headers = $('<tr>');
             var headers = ['Customer Name', 'City', 'Phone', 'Email'];
             
             for(var i = 0; i < headers.length; i++)
             {
                table_headers.append($("<th>" + headers[i] + "</th>"));   
             }
             $('html').css('overflow-y', 'scroll');
             
             $('#panel_list').append($('<table></table>').attr('id', 'sth_list').attr('class', 'table table-fixed'));
             $('table').css('overflow-y', 'scroll');
             $('table').css('height', '500px');
             $('table').css('width', '80%');
             $('table').css('display', 'block');
             $('table').css('border', 'solid 1px');
             $('#sth_list').append($('<thead></thead>').attr('id', 'tab_head'));
             $('#tab_head').append(table_headers);
             $('#sth_list').append($('<tbody></tbody>').attr('id', 'tab_body'));
             
             $.each(data, function(index, value){
                 if(value.phone || value.email){
                    var row_append = $("<tr></tr>");
                    row_append.append($('<td>' + value.firstName + ' ' + value.lastName + '</td>').attr('class', 'col-xs-3'));
                    row_append.append($('<td>' + value.city + '</td>').attr('class', 'col-xs-3'));
                    row_append.append($('<td>' + value.phone + '</td>').attr('class', 'col-xs-3'));
                    row_append.append($('<td>' + value.email +  '</td>').attr('class', 'col-xs-3'));
                    $('#tab_body').append(row_append);
                 }
             });
         },
         error: function(){
            console.log('Non-renewal STH List Failed to Generate');    
         }
    };
    $.ajax(ajax_options);
}

//Will likely need a number year entry parameter to find all previous years (previous working years)
//Also need goals to be sent in from 
function cumulative_year_revenue(){
    var x = Array();
    var y = Array();
    
    var ajax_options = {
        url: web_services.concat(7).concat(year.concat(2016)),
        type: 'GET',
        dataType: 'json',
        
        //Likely will need a function to map for multiple lines
        
        success: function(data){
            var goal_number = 100000.00; //arbitary
            var goals = Array();
            var t = Array();
            var cum_rev = 0;
            var layout = {
                title:'Cumulative Revenue Overlay Years'
            };
            
            $.each(data, function(index, value){
                cum_rev += parseFloat(value.revenue);
                x.push(value.orderDate);
                y.push(value.revenue);
                t.push("2016 Revenue<br>" + "Date: " + new Date(value.orderDate).toDateString() + "<br>" + "Revenue to Date: $" + cum_rev.toFixed(2));
                goals.push(goal_number);
            });
            
            for(var i = 1; i < y.length; i++)
            {
                var k = i - 1;
                y[i]  = parseFloat(y[i]) + parseFloat(y[k]);
            }
            
            var object = {
                x: x,
                y: y,
                text: t,
                hoverinfo: 'text',
                mode: 'lines',
                name: '2016 Revenue'
            };
            
            var goal_line = {
                x: x,
                y: goals,
                text: '2016 Goal<br>$' + goal_number + '.00',
                hoverinfo: 'text',
                mode: 'lines',
                name: '2016 Goal',
                line: {
                    dash: 'dot',
                    width: 4
                }
            };
            var data = [object, goal_line];
            Plotly.newPlot('cumulative_rev', data, layout, {displaylogo: false});
        },  
        error: function() {
            console.log('Cumulative Revenue Failed to Generate');   
        }
    };
    $.ajax(ajax_options);
}



function heat_map_callback(data){
    var x = Array();
    var y = Array();
    var v = Array();
    
    $.each(data, function (index, value){
        if(x.indexOf(value.section) < 0)
           x.push(value.section);
        if(y.indexOf(value.row) < 0)
           y.push(value.row);
    });
    
    for(var i = 0; i < y.length; i++)
    {
      var to_append = Array();
      for(var t = 0; t < x.length; t++)
      {
          to_append[x[t]] = '';
      }
       v[y[i]] = to_append;   
    }
    
    $.each(data, function (index, value){
       v[value.row][value.section] = value.revenue.slice(0, -2);
    });
    
    var z = Array();
    var z1 = Array();
    x.sort();
    y.sort();
    
    for(var i = 0; i < y.length; i++)
    {
        var to_append = Array();
        var to_z = Array();
        
        for(var t = 0; t < x.length; t++)
        {
            to_append.push(v[y[i]][x[t]]);
            if(v[y[i]][x[t]] > 0)
                to_z.push(
                    "Section: " + x[t] + '<br>'
                    + "Row: " + y[i] + '<br>'
                    + 'Revenue: $' + v[y[i]][x[t]]
                );
            else
                to_z.push(
                    "Section: " + x[t] + '<br>'
                    + "Row: " + y[i] + '<br>'
                    + '$' + 0 
                );
        }
        z.push(to_append);
        z1.push(to_z);
    }
    
    var colorscaleValue = [
      [0, '#3D9970'],
      [1, '#001f3f']
    ];
    var data = [{
      x: x,
      y: y,
      z: z,
      text: z1,
      type: 'heatmap',
      hoverinfo: 'text',
      zmin: 0,
      zmax: 8000,
      colorscale: colorscaleValue,
      showscale: true,
      showgrid: false,
      xgap: 5,
      ygap: 3,
      reversescale: true,
    }];
    
    var layout = {
      title: 'Revenue Heatmap By Section/Row',
      annotations: [],
      xaxis: {
        ticks: '',
        showgrid: false,
        side: 'top',
        type: 'category' 
      },
      yaxis: {
        ticks: '',
        ticksuffix: ' ',
        showgrid: false,
        width: 700,
        height: 700,
        autosize: true
      },
      annotations: [{
          xref: 'paper',
          yref: 'paper',
          x: 0,
          xanchor: 'left',
          y: 1,
          yanchor: 'top',
          text: 'Section',
          showarrow: false
        }, {
          xref: 'paper',
          yref: 'paper',
          x: 0,
          xanchor: 'right',
          y: 0,
          yanchor: 'top',
          text: 'Row',
          showarrow: false
        }]
    };
        
    for ( var i = 0; i < y.length; i++ ) {
      for ( var j = 0; j < x.length; j++ ) {
        var currentValue = z[i][j];
        if (currentValue != 0.0) {
          var textColor = 'white';
        }
        else{
          var textColor = 'black';
        }
        var result = {
          xref: 'x1',
          yref: 'y1',
          x: x[j],
          y: y[i],
          text: z[i][j],
          font: {
            family: 'Arial',
            size: 12,
            color: 'rgb(50, 171, 96)'
          },
          showarrow: false,
          font: {
            color: textColor
          }
        };
        layout.annotations.push(result);
      }
    }
    Plotly.newPlot('str_heatmap', data, layout, {displaylogo: false});
}

function heat_map(){
    var x = Array();
    var y = Array();
    var v = Array();
    
    var ajax_options = {
        url: 'https://plotly-test-ryuwa.c9users.io/service_data?action=4',
        type: 'GET',
        dataType: 'json',
        
        success: function(data){
            heat_map_callback(data);
        },
                
        error: function() {
            console.log('Heatmap Failed to Generate');   
        }
    };
$.ajax(ajax_options);     
}   


heat_map();
cumulative_year_revenue();
ticket_revenue_progress();
ticket_average_progress();
non_renewed_list();
renewal_progression();
pacing_by_product();
ticket_available_game();
migration_sankey();
</script>
<script src="{{ secure_asset('c3.min.js') }}"></script>
<script type="text/javascript" src="{{ URL::asset('public/script.js') }}"></script>

</html>