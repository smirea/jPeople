<?php

  require_once 'config.php';
  require_once 'UA-Parser/UAParser.php';

  function track ($column, $value, $failed = false, $error = '') {
    $time = date('Y.m.d H:i:s');
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? 
                      $_SERVER['HTTP_USER_AGENT'] : 'spider';
    $http_referer = isset($_SERVER['HTTP_REFERER']) ? 
                          $_SERVER['HTTP_REFERER'] : '';
    $ua = UA::parse($user_agent);
    $record = array(
      'timestamp' => time(),
      'time' => $time,
      'ip' => get_ip_address(),
      'HTTP_REFERER' => $http_referer,
      'isSpider' => @$ua->isSpider,
      'os' => @$ua->os,
      'osVersion' => @$ua->osVersion,
      'browser' => @$ua->browser,
      'browserVersion' => @$ua->version,
      'device' => @$ua->device,
      'deviceVersion' => @$ua->deviceVersion,
      'isMobile' => @$ua->isMobile,
      'user_agent' => $user_agent,
      'failed' => $failed,
      'error' => $error,
      'query' => $value
    );
    $record = array_map(function ($val) { return "'$val'"; }, $record);
    $query = "INSERT INTO ".TABLE_TRACKING."(".
                        implode(', ', array_keys($record)).
                      ")".
                     " VALUES (".implode(', ', $record).")";
    $result = mysql_query($query);
    return !!$result;
  }

  function get_ip_address () {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }

?>
