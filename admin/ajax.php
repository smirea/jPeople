<?php

  require_once "../config.php";

  if (!isset($_GET['action'])) {
    jsonOutput(array('error' => 'No action specified'));
  }

  switch ($_GET['action']) {
    case 'browsers':
      $query = mysql_query("SELECT DISTINCT browser as category, count(*) AS value
                            FROM Tracking GROUP BY category
                            ORDER BY value DESC"
      );
      sqlToJsonOutput($query);
      break;
    case 'OS':
      $query = mysql_query("SELECT DISTINCT os as category, count(*) AS value
                            FROM Tracking GROUP BY category
                            ORDER BY value DESC"
      );
      sqlToJsonOutput($query);
      break;
    case 'addresses':
      $query = mysql_query("SELECT DISTINCT HTTP_REFERER as category,
                                            count(*) AS value
                            FROM Tracking GROUP BY category
                            ORDER BY value DESC"
      );
      sqlToJsonOutput($query);
      break;
    case 'days-and-hours':
      $query = mysql_query("SELECT time FROM Tracking");
      $data = sqlToArray($query);
      $days = array();
      $hours = array();
      foreach ($data as $value) {
        $tmp = explode(' ', $value['time']);

        if (!isset($days[$tmp[0]])) {
          $days[$tmp[0]] = 0;
        }
        ++$days[$tmp[0]];

        $time = explode(':', $tmp[1]);
        $hour = intval($time[0]);
        if (!isset($hours[$hour])) {
          $hours[$hour] = 0;
        }
        ++$hours[$hour];
      }
      ksort($hours);
      ksort($days);
      jsonOutput(array(
        'hours' => $hours,
        'days' => $days
      ));
      break;
    default:
      jsonOutput(array('error' => 'unknown action `'.$_GET['action'].'`'));
  }

?>