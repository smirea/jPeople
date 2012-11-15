<?php

require_once "../config.php";



?>

<script src="../js/jquery.js"></script>
<script type="text/javascript" src="js/highcharts.js"></script>
<script>
  $(function _on_window_loaded () {
    chart = new Highcharts.Chart({
      chart: {
        renderTo: 'container',
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
      },
      title: {
        text: 'Browser market shares at a specific website, 2010'
      },
      tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage}%</b>',
        percentageDecimals: 1
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            color: '#000000',
            connectorColor: '#000000',
            formatter: function() {
              return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
            }
          }
        }
      },
      series: [{
        type: 'pie',
        name: 'Browser share',
        data: [
        ['Firefox',   45.0],
        ['IE',       26.8],
        {
          name: 'Chrome',
          y: 12.8,
          sliced: true,
          selected: true
        },
        ['Safari',    8.5],
        ['Opera',     6.2],
        ['Others',   0.7]
        ]
      }]
    });
  });
</script>

<div id="container"></div>