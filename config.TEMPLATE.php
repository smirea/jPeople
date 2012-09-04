<?php

  define( 'DB_USER', 'jPerson' );
  define( 'DB_PASS', 'jacobsRulz' );
  define( 'DB_NAME', 'jPeople' );

  define( 'TABLE_RAWDATA', 'RawData' );
  define( 'TABLE_SEARCH', 'Search' );
  define( 'TABLE_TRACKING', 'Tracking' );

  define( 'WEB_ROOT', 'http://localhost/jPeople');

  require_once 'class.Search.php';
  require_once 'utils/query.php';

  dbConnect( DB_USER, DB_PASS, DB_NAME );

  /******************
  ******* URLS ******
  ******************/
  function dataURL( $chr ){
    return "http://swebtst01.public.jacobs-university.de/jPeople/ldap/xml_people_search.php?limit=1000&search=".$chr."&filter=all";
  }

  function birthdayURL( $chr ){
    return "http://swebtst01.public.jacobs-university.de/jPeople/ldap/xml_people_search.php?limit=300&search=$chr&filter=birthday";
  }

  function imageURL( $eid ){
    return "http://swebtst01.public.jacobs-university.de/jPeople/image.php?id=$eid";
  }

  function flagURL( $country ){
    $country = str_replace( " ", '%20', $country );
    return "http://swebtst01.public.jacobs-university.de/jPeople//embed_assets/flags/$country.png";
  }

  /******************
  ****** COLUMNS ****
  ******************/
  $map = array(
    'employeeid'                  => 'eid',
    'company'                     => 'employeetype',
    'samaccountname'              => 'account',
    'employeetype'                => 'attributes',
    'givenname'                   => 'fname',
    'sn'                          => 'lname',
    'displayname'                 => 'displayname',
    'name'                        => 'name',
    'cn'                          => 'cn',
    'houseidentifier'             => 'college',
    'extensionattribute2'         => 'majorinfo',
    'extensionattribute3'         => 'majorlong',
    'extensionattribute5'         => 'country',
    'mail'                        => 'email',
    'roomInfo'                    => 'room',
    'telephonenumber'             => 'phone',
    'description'                 => 'description',
    'title'                       => 'title',
    'physicaldeliveryofficename'  => 'office',
    'department'                  => 'department',
    'wwwhomepage'                 => 'www',
    'jpegphoto'                   => 'photo',
    'deptInfo'                    => 'deptinfo'
  );

  $search = array(
    'eid', 'employeetype', 'account', 'attributes', 'fname', 'lname', 'birthday', 'country', 'college',
    'majorlong', 'majorinfo', 'room', 'phone', 'email', 'description', 'title', 'office', 'deptinfo'
  );

  $search_query = array(
    'fname', 'lname', 'college', 'room', 'phone', 'country',
    'major', 'birthday', 'year', 'status'
  );

  $searchable_columns = array(
    'employeetype', 'account', 'attributes', 'fname', 'lname', 'birthday', 'country',
    'college', 'majorlong', 'majorinfo', 'room', 'phone', 'description',
    'title', 'office', 'deptinfo', 'major', 'block', 'floor', 'email', 'year', 'status'
  );

  /******************
  ******* BULK ******
  ******************/

  function sqlToJsonOutput( $q ){
    if( $q ){
      jsonOutput( sqlToArray( $q ) );
    } else {
      jsonOutput( array( 'error' => mysql_error() ) );
    }
  }

  function jsonOutput( array $arr ){
    if( !headers_sent() ){
      header('Cache-Control: no-cache, must-revalidate');
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
      header('Content-type:application/json');
      header('Content-attributes: application/json; charset=ISO-8859-15');
    }
    exit( json_encode( $arr ) );
  }

  function sqlToArray( $sql, $key = null ){
    if( $sql ){
      $a = array();
      while( $r = mysql_fetch_assoc( $sql ) ){
        if( $key ){
          $a[ $r[ $key ] ] = $r;
        } else {
          $a[] = $r;
        }
      }
      return $a;
    } else {
      return array();
    }
  }

  function dbConnect($user, $pass, $name = null, $host = 'localhost'){
    $connexion = mysql_connect( $host, $user, $pass ) or die ("Could not connect to Data Base!");
    if( $name ) mysql_select_db( $name, $connexion ) or die ("Failed to select Data Base");
  }

  /******************
  ****** SEARCH *****
  ******************/

  $Search = new Search( $searchable_columns );
  $Search->addHook( 'parse_after', 'courses', 'SearchHook_courses_parse' );
  $Search->addHook( 'getQuery_after', 'courses', 'SearchHook_courses_getQuery' );

  function SearchHook_courses_getQuery( &$Sender, &$strict, &$ambiguous, &$raw ){
    $data = $Sender->getHookData( 'courses/parse' );
    if( $data && strpos( $data['query'], 'IN ()' ) === false ){
      $raw .= ( strlen($raw) > 2 ? ' AND ' : '' );
      $raw .= " ".$data['query'];
    }
  }

  function SearchHook_courses_parse( &$Sender, array &$tokens ){

    if( isset($tokens['strict']['course']) ){
      $q = "SELECT id,number,name FROM Courses WHERE ";
      $restrictions = array();
      foreach( $tokens['strict']['course'] as $v ){
        foreach( $v as $val ){
          $restrictions[] = " name LIKE '%$val%'";
        }
      }
      $q .= implode(' OR ', $restrictions );
      $q = mysql_query( $q );
      $courses  = array();
      $students = array();
      while( $r = mysql_fetch_assoc( $q ) ){
        if( isset( $courses[ $r['name'] ] ) ){
          continue;
        }
        $students[ $r['name'] ] = getStudentsByCourse( $r['id'] );
        //$students['_fromCourse'] = $r['name'];
        $courses[ $r['name'] ] = $r;
        $courses[ $r['name'] ]['_students'] = $students;
      }

      $coursesKeys  = array_keys( $courses );
      $fullNameArr  = array();
      foreach( $tokens['strict']['course'] as $k => $v ){
        $fullNameArr[ $k ] = array();
        foreach( $v as $k => $val ){
          foreach( $coursesKeys as $c ){

          }
        }
      }

      $and = array();
      foreach( $tokens['strict']['course'] as $k => $v ){
        $or = array();
        foreach( $v as $k2 => $val ){
          $or = array_merge( $or, getStudentsEids( $students, $val ) );
        }
        $or = array_unique( $or );
        $and[] = "eid IN (".implode(', ', $or).")";
      }
      $query = count($and) > 0 ? implode( ' AND ', $and ) : '';

      $Sender->setHookData( 'courses/parse', array(
        'query' => $query
      ));
      unset( $tokens['strict']['course'] );
    }

  }


  function getStudentsEids( $students, $course ){
    $ids = array();
    foreach( $students as $k => $v ){
      if( stripos( $k, $course ) !== false ){
        foreach( $v as $st ){
          $ids[ $st['eid'] ] = true;
        }
      }
    }
    $ids = array_keys( $ids );

    return $ids;
  }


?>
