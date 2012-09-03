<?php

  require_once 'config.php';
  require_once 'VCF.php';

  if(!isset($_GET['action']) || strlen($_GET['action']) < 2) {
    jsonOutput( array('error' => '[ERROR] No action set') );
  }

	define('MIN_LIMIT', 3);

	if( isset($_GET['str']) ) { //actions that require additional info

    $str 		= $_GET['str'];

    if( strlen( $str ) < MIN_LIMIT ){
      jsonOutput( array('error' => 'Query too short. Must have at least '.MIN_LIMIT.' chars') );
    }

    switch($_GET['action']){
      case 'autoComplete':
        $columns  = "id,eid,fname,lname,country,major,college";
        if( $clause = $Search->getQuery($str) ){
          $res      = mysql_query( "SELECT $columns FROM ".TABLE_SEARCH." WHERE $clause" );
          sqlToJsonOutput( $res );
        } else {
          jsonOutput( array( 'error' => 'Invalid query' ) );
        }
      break;
      case 'fullAutoComplete':
        $columns  = 'id,eid,employeetype,attributes,account,attributes,fname,lname,birthday,country,college,majorlong,'.
                    'majorinfo,major,status,year,room,phone,email,description,title,office,deptinfo,block,floor';
        if( $clause = $Search->getQuery($str) ){
          $res      = mysql_query( "SELECT $columns FROM ".TABLE_SEARCH." WHERE $clause" );
          $records  = sqlToArray($res);
          foreach ($records as $key => $value) {
            $records[$key]['photo_url'] = imageUrl($value['eid']);
            $records[$key]['flag_url'] = flagURL($value['country']);
          }

          jsonOutput(array(
            'sanitize'  => $Search->getLastSanitize(),
            'parse'     => $Search->getLastParse(),
            'length'    => mysql_num_rows( $res ),
            'clause'    => $clause,
            'records'   => $records
          ));
        } else {
          jsonOutput( array( 'error' => 'Invalid query' ) );
        }
      break;
      case 'getFace':
        $columns  = 'id,eid,employeetype,attributes,account,attributes,fname,lname,birthday,country,college,majorlong,'.
                    'majorinfo,major,status,year,room,phone,email,description,title,office,deptinfo,block,floor';
        $res      = mysql_query( "SELECT $columns FROM ".TABLE_SEARCH." WHERE eid='$str'" );
        sqlToJsonOutput( $res );
      break;
      case 'getAll':
        $columns  = "*";
        $res      = mysql_query( "SELECT $columns FROM ".TABLE_SEARCH." WHERE ".$Search->getQuery($str) );
        sqlToJsonOutput( $res );
      break;
      case 'vcf':
        $str = massVCF( explode('_', $str ) );
        if( $str ){
          if( !headers_sent() ){
            header('Content-Description: File Transfer');
            header('Content-Type: text/x-vcard; charset=utf-8');
            header('Content-Disposition: attachment; filename=jPeople_contacts.vcf');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . strlen($str) );
            ob_clean();
            flush();
            echo $str;
            exit;
          } else {
            jsonOutput( array('error' => '[ERROR] Headers already sent') );
          }
        } else {
          jsonOutput( array('error' => '[ERROR] '.mysql_error() ) );
        }
      break;
      default: jsonOutput( array( 'error' => 'No search string specified' ) );
    }

  } else {
    jsonOutput( array( 'error' => 'No search string specified' ) );
  }

?>
