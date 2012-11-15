<?php

require_once "../config.php";



?>

<script src="../js/jquery.js"></script>
<script type="text/javascript" src="js/highcharts.js"></script>
<script>
  var $charts = {};
  $(function _on_window_loaded () {
    make_default_chart_containers($('body'), [
      'browsers', 'OS', 'addresses', 'days', 'hours'
    ]);

    update_category_pie_chart ('browsers', 'Browser Ratio', $charts.browsers[0]);
    update_category_pie_chart ('OS', 'OS Ratio', $charts.OS[0], 0.001);

    $.get('ajax.php', {action: 'addresses'}, function (response) {
      var i;
      var map = {};
      var link = document.createElement('a');
      var host;
      for (i=0; i<response.length; ++i) {
        if (!response[i].category) {
          host = '(empty)';
        } else {
          link.href = response[i].category;
          host = link.hostname.replace(/^www\./i, '');
        }
        map[host] = map[host] || 0;
        map[host] += Number(response[i].value);
      }
      var data = [];
      for (var key in map) {
        data.push([key, Number(map[key])]);
      }
      console.log(data);
      category_pie_chart('HTTP_REFERER', $charts.addresses[0], data);
    });

    $.get('ajax.php', {action: 'days-and-hours'}, function (response) {
      category_line_chart('Daily activity', $charts.days[0], response.days);
      category_line_chart('Hourly activity', $charts.hours[0], response.hours);
    });

  });

  function make_default_chart_containers ($container, names) {
    var i;
    for (i=0; i<names.length; ++i) {
      $charts[names[i]] = jq_element('div');
      $charts[names[i]].attr({
        'class': 'chart-container',
        'chart-name': names[i]
      });
      $container.append($charts[names[i]])
    }
  }

  function update_category_pie_chart (action, title, target, min_percent) {
    // anything lower than 0.5 % will be added to "Other"
    min_percent = min_percent === undefined ? 0.005 : min_percent;

    return $.get('ajax.php', {
      action: action
    }, function (response) {
      var tmp = {};
      var data = [];
      var i;
      var value;
      var total = 0;
      var name;
      var other = 0;
      for (i=0; i<response.length; ++i) {
        value = Number(response[i].value);
        total += value;
        if (response[i].category) {
          tmp[response[i].category] = value;
        } else {
          other += value;
        }
      }
      for (name in tmp) {
        if (tmp[name] < min_percent * total) {
          other += tmp[name];
        } else {
          data.push([name, tmp[name]]);
        }
      }
      data.push(['Other', (tmp.Other || 0) + other]);
      category_pie_chart(title + ' (total: '+total+')', target, data);
    });
  }

  function category_line_chart (title, target, data) {
    return new Highcharts.Chart({
      chart: {
        renderTo: target,
        plotShadow: true
      },
      title: {
        text: title
      },
      xAxis: {
        categories: Object.keys(data)
      },
      tooltip: {
        formatter: function() {
          return '<b>'+this.y+'</b><br />('+this.x+')'
        }
      },
      series: [{
        name: 'value',
        data: object_get_values(data)
      }]
    });
  }

  function category_pie_chart (title, target, data) {
    return new Highcharts.Chart({
      chart: {
        renderTo: target,
        plotShadow: true
      },
      title: {
        text: title
      },
      tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage}%</b>',
        percentageDecimals: 2
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          cursor: 'pointer',
          showInLegend: true,
          dataLabels: {
            enabled: true,
            color: '#000000',
            connectorColor: '#000000',
            formatter: function() {
              return '<b>'+ this.point.name +'</b>: '+
                      this.percentage.toFixed(2) +' % ('+this.y+')';
            }
          }
        }
      },
      series: [{
        type: 'pie',
        name: 'share',
        data: data
      }]
    });
  }

  function object_get_values (obj) {
    var key;
    var arr = [];
    for (key in obj) {
      arr.push(obj[key]);
    }
    return arr;
  }

  function jq_element (type) {
    return $(document.createElement(type));
  }
</script>

<body>

</body>