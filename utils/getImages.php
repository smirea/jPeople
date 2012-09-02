<?php

require_once '../config.php';

define("DIR_IMAGES", "images/");	// eding "/"
define("FILE_DATA", "jP-data.xml");

if(!is_dir(DIR_IMAGES)){
	try{
		mkdir(DIR_IMAGES);
		chmod(DIR_IMAGES, 0777);
	} catch(Exception $E) {
		exit("<b>Fatal error:</b> Unable to creade image directory!");
	}
}

if(!is_readable(FILE_DATA)) exit("<b>Fatal error:</b> Data file unexistent or not enough permissions to read it!");

$f = file_get_contents( FILE_DATA );

preg_match_all('/<name>.+<\/name>/',$f, $tmp);
$arr = array();
foreach($tmp[0] as $v){
	$v = str_replace(array('<extensionattribute5>', '</extensionattribute5>'), array('', ''), $v);
	$v = substr($v, strrpos($v, '(')+1);
	$v = substr($v, 0, strrpos($v, ')'));
	$arr[$v] = true;
}
$a = Array();
foreach($arr as $k => $v) $a[] = $k;
sort($a);
echo "<ol>";
//foreach($a as $v) echo "<li>$v</li>";

foreach($a as $v) {
	echo "<li>$v (" . imageUrl($v) . ")</li>";
	file_put_contents(DIR_IMAGES."$v.jpg", file_get_contents( imageURL( $v ) ));
}
echo "</ol>";

?>
