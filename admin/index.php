<?php

require_once "../config.php";



?>

<style>
  .data-table {
    border-collapse: collapse;
    border: 1px solid #ccc;
    background: #fafafa;
    font-family: "Lucida Grande", Verdana, Arial;
    font-size: 9pt;
  }
  .data-table tr:nth-child(2n) {
    background-color: rgba(60, 120, 220, 0.5);
  }
  .data-table td {
    word-wrap: break-word;
    text-overflow: ellipsis;
    max-width: 400px;
    padding: 1px 2px;
  }
  .data-table td a {
    text-decoration: none;
    color: #039;
  }
  .data-table td a:hover {
    background: yellow;
  }
  .data-table td .host {
    display: inline-block;
    margin-right: 2px;
    font-weight: bold;
    text-decoration: inherit;
    color: #900;
  }
</style>
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
      var key;
      var data;
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
      data = [];
      for (key in map) {
        data.push([key, Number(map[key])]);
      }
      category_pie_chart('HTTP_REFERER', $charts.addresses[0], data);

      // create table
      map = {};
      for (i=0; i<response.length; ++i) {
        host = response[i].category.replace(/^\s*(https?:\/\/)?(www\.)?/i, '');
        host = host.replace(/\?.*$/i, '');
        host = host || '(empty)';
        map[host] = map[host] || 0;
        map[host] += Number(response[i].value);
      }
      var $table = jq_element('table').addClass('data-table');
      $table.append('<tr><th>HTTP_REFERER</th><th>requests</th>');
      $table.insertAfter($charts.addresses);
      for(key in map) {
        $table.append('<tr>'+
                        '<td>'+
                          '<a href="http://'+key+'" target="_blank">'+
                            key.replace(/([^\/]+\/)/,'<span class="host">$1</span>')+
                          '</a>'+
                        '</td>'+
                        '<td>'+map[key]+'</td>'+
                      '</tr>'
        );
      }
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