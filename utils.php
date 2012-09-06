<?php

  require_once 'config.php';
  require_once 'UA-Parser/UAParser.php';

  function track ($column, $value, $failed = false, $error = '') {
    $time = date('Y.m.d H:i:s');
    $ua = UA::parse();
    $query = "INSERT INTO ".TABLE_TRACKING."(timestamp, time, ip, os, browser,
                            browser_version, user_agent, failed, error, query)".
                     " VALUES ('".time()."', '$time', '".get_ip_address().
                                "', '".$ua->os."', '". $ua->browser.
                                "', '".$ua->version."', '".
                                $_SERVER['HTTP_USER_AGENT'].
                                "', '$failed', '$error', '$value')";
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
