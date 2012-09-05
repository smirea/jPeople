<?php

  /** REQUIRES: config.php **/
  require_once 'config.php';

  function track ($column, $value, $failed = false, $error = '') {
    $time = date('Y.m.d H:i:s');
    $query = "INSERT INTO ".TABLE_TRACKING."(timestamp, ip, failed, error, query) ".
                     "VALUES ('".time()."', '$time', '".get_ip_address()."', '$failed', '$error', '$value')";
    return mysql_query($query);
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
