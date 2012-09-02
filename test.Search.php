<form method="get">
  <textarea cols="50" rows="10" type="text" name="q" /><?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?></textarea>
  <br />
  <input type="submit" value="Query the awesomeness!" />
</form>
<hr />
<?php

  require_once 'config.php';
  
  if( isset($_GET['q']) ){
    
    $tokens = $Search->parse( $_GET['q'] );
    echo "<pre>";
    var_export( $Search->sanitize( $_GET['q'] ) );
    echo "\n";
    var_export( $tokens );
    echo "</pre>";
    
    $q = " SELECT id,eid,fname,lname,major,country FROM ".TABLE_SEARCH." WHERE " . $Search->getQuery( $_GET['q'] );
    echo "$q<hr />";
    $q = mysql_query( $q );
    if( !$q ){
      exit( mysql_error() );
    }
    echo '<ol>';
    while( $r = mysql_fetch_assoc($q) ){
      echo <<<ROW
      <li>$r[fname] $r[lname] - $r[country] / $r[major]</li>
ROW;
    }
    echo '</ol>';
  }
  
?>
