<?php

  require_once '../config.php';

  $college  = 'Mercator';
  $cID      = 'm';
  $blocks   = array( 'a', 'b', 'c' );
  $floors   = array( 1, 2, 3 );

  function getTables( $college, $cID, array $blocks, $floors ){
    $data = array();

    foreach( $blocks as $B ){
      $roomCode = "Mercator $B Block";
      $q = mysql_query( "SELECT eid,fname,lname,room,country
                          FROM ".TABLE_SEARCH."
                          WHERE block='$B' AND college='$college'" );
      if( $q ){
        $data[ $roomCode ] = array();
        while( $r = mysql_fetch_assoc( $q ) ){
          $data[ $roomCode ][$r['fname'].' '.$r['lname']] = $r;
        }
      } else {
        echo "$roomCode: ".mysql_error()."<hr />\n";
      }
    }

    $h = '';
    foreach( $data as $floor => $people ){
      ksort( $people );
      $h .= '<table class="floorSign" cellspacing="1" cellpadding="0">';
      $h .= "<tr><th colspan=\"10\"> $floor </th></tr>";
      $h .= makeSign( array_values( $people ) );
      $h .= "</table>\n\n";
    }
    
    return $h;
  }

  function makeSign( array $a ){
    $h = '';
    $half = floor( count($a)/2 );
    for( $i=0; $i <= $half; ++$i ){
      $v = $a[$i];
      $w = array(
        'eid'     => '',
        'fname'   => '',
        'lname'   => '',
        'room'    => '',
        'country' => ''
      );
      $photo2 = imageURL( '0.png' );
      if( $i + $half < count( $a ) ){
        $w      = $a[ $half + $i ];
        $photo2 = imageURL( $w['eid'] );
      }
      $photo = imageURL( $v['eid'] );
      $h .= <<<ROOM
        <tr>
          <td rowspan="3" class="photo"><img src="$photo" /></td>
          <td class="name">$v[fname] $v[lname]</td>
          <td class="name" style="text-align:right">$w[fname] $w[lname]</td>
          <td rowspan="3" class="photo"><img src="$photo2" /></td>
        </tr>
        <tr>
          <td class="room">$v[room]</td>
          <td class="room" style="text-align:right">$w[room]</td>
        </tr>
        <tr>
          <td class="country">$v[country]</td>
          <td class="country" style="text-align:right">$w[country]</td>
        </tr>
        <tr class="delimiter">
          <td colspan="10">&nbsp;</td>
        </tr>
ROOM;
    }
    
    return $h;
  }
  
?>
<style>
  * {
    font-family : verdana, arial;
  }
  .floorSign{
    background  : #aaa;
    font-size   : 10pt;
    margin-bottom : 100px;
  }
  .floorSign th{
    background      : #346989;
    padding         : 5px;
    font-size       : 20px;
    font-weight     : bold;
    text-align      : center;
    text-transform  : uppercase;
    color           : #fff;
  }
  .floorSign td{
    background  : #fff;
    padding     : 1px 5px;
  }
  .name{
    background  : lightblue!important;
    max-width   : 200px;
    font-weight : bold;
  }
  .photo{
    background  : #ccc!important;
    text-align  : center;
    padding     : 0!important;
  }
  .photo img{
    max-height  : 100px;
    max-width   : 80px;
  }
  .delimiter td{
    background  : orange;
    line-height : 3px;
  }
</style>

<?php

  if( !headers_sent() ){
    header('Content-Type: text/html; charset=utf-8');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo getTables( $college, $cID, $blocks, $floors );
  }
  
?>