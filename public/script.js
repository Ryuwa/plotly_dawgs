    //Ticket Revenue Progress to Goal
    var y1 = [];
    var y2 = [];
    
    y1.push(1);
    y2.push(1);
    
    var current =
     {
        x: [10],
        y: y1,
        type: 'histogram',
        opacity: 0.5,
        marker: {
          color: 'green',  
        },
     };
    
    var goal = 
    {
        x: [11],
        y: y2,
        type: 'histogram',
        opacity: 0.6,
    	marker: {
        color: 'pink',
    	},
     };
    
    var data = [current, goal];
    var layout = {
        barmode: "overlay",
        xaxis: {range: [1, 11]},
        yaxis: {range: [1, 1]}
    };
    Plotly.newPlot('revenue_progress', data, layout);
    
    
    var xValues = ['A', 'B', 'C', 'D', 'E'];

var yValues = ['W', 'X', 'Y', 'Z'];

var zValues = [
  [0.00, 0.00, 0.75, 0.75, 0.00],
  [0.00, 0.00, 0.75, 0.75, 0.00],
  [0.75, 0.75, 0.75, 0.75, 0.75],
  [0.00, 0.00, 0.00, 0.75, 0.00]
];

var colorscaleValue = [
  [0, '#3D9970'],
  [1, '#001f3f']
];

var data = [{
  x: xValues,
  y: yValues,
  z: zValues,
  type: 'heatmap',
  colorscale: colorscaleValue,
  showscale: true
}];

var layout = {
  title: 'Sellthrough Rate By Section and Row',
  annotations: [],
  xaxis: {
    ticks: '',
    side: 'top'
  },
  yaxis: {
    ticks: '',
    ticksuffix: ' ',
    width: 700,
    height: 700,
    autosize: false
  }
};

for ( var i = 0; i < yValues.length; i++ ) {
  for ( var j = 0; j < xValues.length; j++ ) {
    var currentValue = zValues[i][j];
    if (currentValue != 0.0) {
      var textColor = 'white';
    }else{
      var textColor = 'black';
    }
    var result = {
      xref: 'x1',
      yref: 'y1',
      x: xValues[j],
      y: yValues[i],
      text: zValues[i][j],
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

Plotly.newPlot('str_heatmap', data, layout);