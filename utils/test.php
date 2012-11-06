<?php
  
  echo 'http://' . $_SERVER['HTTP_HOST'] . '/' . dirname($_SERVER['SCRIPT_NAME']);
  echo '<hr />';

  var_export($_SERVER);

?>
