<?php

  require_once 'config.php';
  

  if (!isset($_GET['type'])) {
    jsonOutput(array('error' => 'no type specified'));
  }
  
  $css = array('css/jquery-ui/jquery-ui.css', 'css/jPeople.css', 'css/tCheckbox.css');
  $js = array("js/jquery.js", "js/jquery-ui.js", "js/jquery.jqprint.js",
              "js/tCheckbox.js", "js/jPeople.js"
  );

  $exclude = isset($_GET['exclude']) ? $_GET['exclude'] : array();
  switch ($_GET['type']) {
    case 'list-css':
      jsonOutput(array('list' => $css));
      break;
    case 'list-js':
      jsonOutput(array('list' => $js));
      break;
    case 'css':
      header('Content-Type: text/css');
      echo merge_files($css, $exclude);
      break;
    case 'js':
      header('Content-Type: application/x-javascript');
      echo merge_files($js, $exclude);
      break;
    default:
      jsonOutput(array('error' => 'invalid type'));
  }

  function merge_files (array $files, array $exclude = array()) {
    $content = array();
    foreach ($files as $path) {
      if (in_array($path, $exclude)) {
        continue;
      }
      $content[] = "/********\n" . 
        " | File: $path\n" .
        "********/\n\n" . 
        file_get_contents($path) .
        "\n\n/** End File: $path **/";
    }
    return implode("\n\n", $content);
  }

?>
