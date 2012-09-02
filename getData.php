<?php

require_once '../config.URLs.php';

define("FILE_DATA", "jP-data.xml");
define("FILE_BIRTHDAYS", "jP-bDays.dump");
define("SUFFIX", "@");

fclose(fopen(FILE_DATA, "w"));
fclose(fopen(FILE_BIRTHDAYS, "w"));

chmod(FILE_DATA, 0777);
chmod(FILE_BIRTHDAYS, 0777);

$h = '';
for($i=97; $i<=122; ++$i){
	$chr	= urlencode(chr($i).SUFFIX);
	echo "$chr<br />";
	$href = dataURL( $chr );
	$h .= file_get_contents($href);
}
file_put_contents(FILE_DATA, $h);

echo "<hr />";

// Birthdays
$h 		= '';
$find		= array("<People>\n", "</People>\n");
$nameTag = 'employeeid';
for($i=1; $i<=12; ++$i){
	for($j=1; $j<=31; ++$j){
		$chr	= "$j.$i";
		echo "Date >: $chr <br />";
		$href	 = birthdayURL( $chr );
		$cont	 = file_get_contents($href);
		$cont  = str_replace($find, "", $cont);
		preg_match_all("/<$nameTag>([^<]*)<\/$nameTag>/", $cont, $a);
		foreach($a[1] as $v){
			$h .= "$chr $v\n";
		}
		
	}
}
file_put_contents(FILE_BIRTHDAYS, $h);

echo "<hr />--- DONE ---";

?>
