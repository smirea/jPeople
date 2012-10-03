<?php

  require_once 'config.php';

  foreach ($_GET as $key => $value) {
    $_GET[$key] = addslashes($value);
  }

  $values = array_flip(explode(' ', 'name email type message'));
  foreach ($values as $key => $value) {
    $values[$key] = "'" . $_GET[$key] . "'";
  }
  $values['time'] = date("'Y.m.d H:i:s'");

  $query = "INSERT INTO Feedback (".implode(', ', array_keys($values)).") " .
              "VALUES (".implode(', ', $values).")";

  jsonOutput(array(
    'result' => mysql_query($query),
    'error' => mysql_error(),
    'query' => $query
  ));

?>