<?php

  if( !headers_sent() ){
    header('Content-Type: text/html; charset=utf-8');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
  }

  require_once '../config.php';

  $college  = 'Mercator';
  $cID      = 'm';
  $blocks   = array( 'a', 'b', 'c', 'd' );
  $floors   = array( 1, 2, 3 );

  function getTables( $college, $cID, array $blocks, array $floors ){
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
      $a  = array_values( $people );
      if (count($people) >= 40) {
        $h .= makeSign( $floor, array_slice( $a, 0, count( $a ) / 2 ) );
        $h .= makeSign( $floor, array_slice( $a, count( $a ) / 2 ) );
      } else {
        $h .= makeSign( $floor, $a );
      }
    }

  return $h;
  }

  function makeSign( $title, array $a ){
    $h = '';
    $h .= '<table class="floorSign" cellspacing="1" cellpadding="0">';
    $h .= "<tr><th colspan=\"10\"> $title </th></tr>";
    for( $i=0; $i < count( $a ); $i += 2 ){
      $v = $a[$i];
      $w = array(
        'eid'     => '',
        'fname'   => '',
        'lname'   => '',
        'room'    => '',
        'country' => ''
      );
      $photo2 = imageURL( '0' );
      if( $i < count( $a ) - 1 ){
        $w      = $a[ $i + 1 ];
        $photo2 = imageURL( $w['eid'] );
      }
      $photo = imageURL( $v['eid'] );
      $h .= <<<ROOM
        <tr>
          <td rowspan="6" class="photo" style="background:white!important"><img src="$photo" /></td>
          <td class="name">$v[fname] $v[lname]</td>
          <td rowspan="6" class="photo" style="background:lightblue!important; "><img src="$photo2" /></td>
        </tr>
        <tr>
          <td class="room">$v[room]</td>
        </tr>
        <tr>
          <td class="country">$v[country]</td>
        </tr>
        <tr>
          <td class="name" style="background:lightblue; text-align:right">$w[fname] $w[lname]</td>
        </tr>
        <tr>
          <td class="room" style="background:lightblue; text-align:right">$w[room]</td>
        </tr>
        <tr>
          <td class="country" style="background:lightblue; text-align:right">$w[country]</td>
        </tr>
        <tr class="delimiter">
          <td colspan="3">&nbsp;</td>
        </tr>
ROOM;
    }
    $h .= "</table>\n\n";

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
    /* background  : lightblue!important; */
    max-width   : 200px;
    font-weight : bold;
    font-size   : 11pt;
  }
  .photo{
    background  : #ccc!important;
    text-align  : center;
    padding     : 0!important 5px;
    /* padding     : 0!important; */
  }
  .photo img{
    max-height  : 115px;
    max-width   : 100px;
  }
  .delimiter td{
    background  : orange;
    line-height : 3px;
  }
</style>

<script src="../js/jquery.js"></script>
<script src="../js/jquery.jqprint.js"></script>
<script>
  $(function(){
    $('#printAll').click(function(){
      $('#print').jqprint();
    });
  });
</script>

<div id="print">
<?php
    echo getTables( $college, $cID, $blocks, $floors );
?>
</div>