<?php

  require_once( 'config.php' );

  function massVCF( array $eids ){
    $data = getVCFData( $eids );
    if( is_array( $data ) ){
      $a = array();
      foreach( $data as $v ){
        $a[] = VCF( $v );
      }
      return implode( "\r\n", $a );
    } else {
      //var_dump( $data );
      return null;
    }
  }

  function getVCFData( array $eids ){
    $columns    = 'eid,fname,lname,email,phone,status,room,country';
    $condition  = array();
    foreach( $eids as $v ){
      $condition[] = "eid='$v'";
    }
    $condition = implode( ' OR ', $condition );
    $q = mysql_query( "SELECT $columns FROM ".TABLE_SEARCH." WHERE $condition" );
    if( $q ){
      $a = array();
      while( $r = mysql_fetch_assoc( $q ) ){
        $a[] = $r;
      }
      return $a;
    } else {
      return mysql_error();
    }
  }

  function VCF( array $data ){
    foreach( $data as $k => $v ){
      $$k = utf8_encode( $v );
    }

    $fullname = "$fname $lname";
    $lname    = implode(';', explode(' ', $lname) );
    $fname    = implode(';', explode(' ', $fname) );
    $phone    = strlen( $phone ) == 4 ? "0049421200$phone" : $phone;
    $photo    = chunk_split( base64_encode( file_get_contents( imageURL($eid) ) ) );
    $photo    = explode( "\r\n", $photo );
    foreach( $photo as $k => $v ){
      $photo[$k] = "  $v";
    }
    array_pop( $photo );
    $photo = implode( "\n", $photo );

    $VCF = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:$lname;$fname;
FN:$fullname
EMAIL;type=INTERNET;type=Jacobs;type=pref:$email
TEL;type=Jacobs;type=pref:$phone
item2.X-ABRELATEDNAMES;type=pref:$status
item2.X-ABLabel:Status
item1.ADR;type=HOME;type=pref:;;$room;;;;
item1.X-ABADR:de
NOTE:From $country
PHOTO;BASE64:
$photo
END:VCARD
VCF;
    return $VCF;
  }

?>