<?php

require_once '../config.php';

define("DIR_FLAGS", "flags/");	// eding "/"

if(!is_dir(DIR_FLAGS)){
	try{
		mkdir(DIR_FLAGS);
		chmod(DIR_FLAGS, 0777);
	} catch(Exception $E) {
		exit("<b>Fatal error:</b> Unable to creade flag directory!");
	}
}

$q = mysql_query( "SELECT DISTINCT country FROM Search" ) or die( mysql_error() );
echo "Total: ".mysql_num_rows( $q ).'<br />';
$success  = array();
$fail     = array();
while( $r = mysql_fetch_assoc( $q ) ){
  $v = $r['country'];
  $img = file_get_contents( flagURL( $v ) );
  if( $img ){
    file_put_contents(DIR_FLAGS."$v.png", $img );
    $success[] = $v;
  } else {
    $fail[] = $v;
  }
}

echo "Failed: <ol>";
foreach( $fail as $v ){
  echo "<li>$v</li>";
}
echo "</ol> <hr />";

echo "Downloaded: <ol>";
foreach( $success as $v ){
  echo "<li>$v</li>";
}
echo "</ol> <hr />";

?>
