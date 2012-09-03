<?php

  /**
   * How the parsing works:
   * - a b            => query LIKE '%a%' AND query LIKE '%b%'
   * - a:b            -> a LIKE '%b%'
   * - a:"b"          -> a LIKE '%b%'
   * - a:~b,c         -> (a NOT LIKE '%b%' OR a LIKE '%c%')
   * - a:b,c,d        -> (a LIKE '%b%' OR a LIKE '%c%' OR a LIKE '%d%')
   * - a:b,c a:d      -> (a LIKE '%b%' OR a LIKE '%c%') AND (a LIKE '%d%')
   * - x a:b,c,d y    -> (a LIKE '%b%' OR a LIKE '%c%' OR a LIKE '%d%') AND (query LIKE '%x%' AND query LIKE '%y%')
   */
  class Search{

    const MIN_AMBIGUOUS_TOKEN_LENGTH = 2;
    const MIN_STRICT_TOKEN_LENGTH = 1;

    private $fields         = array();
    private $hooks          = array();
    private $hookData       = array();
    private $_lastSanitize  = '';
    private $_lastParse     = array();

    public function __construct( array $fields ){
      $this->setFields( $fields );
    }

    /**
     * @param {mixed} $val  The query to parse. Can either be :
     *                        - a string in which case it is parsed
     *                        - an array resulted from Search::parse()
     * @return {string} The SQL conditions for the given query
     */
    public function getQuery( $val, $skipTableCheck = false ){

      $tokens = Search::parse( $val );
      if( !$tokens ){
        return null;
      }

      $this->triggerHook( 'getQuery_before', $tokens, $skipTableCheck );

      if( $skipTableCheck || !$this->getFields() ){
        $tables = $this->getFields();
        foreach( $tokens['strict'] as $k => $v ){
          if( !in_array( $v, $tables ) ){
            unset( $tokens['strict'][$k] );
          }
        }
      }

      $ambiguous = array();
      foreach( $tokens['ambiguous'] as $v ){
        if( strlen($v) >= Search::MIN_AMBIGUOUS_TOKEN_LENGTH ){
          if( substr( $v, 0, 1 ) == '~' ){
            $ambiguous[] = "query NOT LIKE '%".ltrim($v,'~')."%'";
          } else {
            $ambiguous[] = "query LIKE '%$v%'";
          }
        }
      }
      $ambiguous = '('.implode(' AND ', $ambiguous ).')';

      $strict = array();
      foreach( $tokens['strict'] as $k => $v ){
        $column = array();
        foreach( $v as $v2 ){
          $field = array();
          foreach( $v2 as $val ){
            if( strlen($val) >= Search::MIN_STRICT_TOKEN_LENGTH ){
              if( substr( $val, 0, 1 ) == '~' ){
                $field[] = "$k NOT LIKE '".ltrim($val, '~')."%'";
              } else {
                $field[] = "$k LIKE '$val%'";
              }
            }
          }
          $column[] = '('.implode( ' OR ', $field ).')';
        }
        $column = '('.implode( ' AND ', $column ).')';
        $strict[] = $column;
      }
      $strict = implode(' AND ', $strict);

      $raw = '';
      if( isset( $tokens['raw'] ) ){
        $raw = $tokens['raw'];
      }

      $this->triggerHook( 'getQuery_after', $strict, $ambiguous, $raw );

      if( strlen($strict) <= 4 && strlen($ambiguous) <= 2 && strlen($raw) <= 2 ){
        return null;
      } else {
        $return = '';

        $and = false;
        if( strlen( $strict ) > 4 ){
          $return .= " $strict";
          $and = true;
        }
        if( strlen( $ambiguous ) > 2 ){
          $return .= ( $and ? ' AND ' : '' ) . " $ambiguous";
          $and = true;
        }
        if( strlen( $raw ) > 2 ){
          $return .= ( $and ? ' AND ' : '' ) . " $raw";
          $and = true;
        }
        return $return;
      }

    }

    public function parse( $str ){
      $str = Search::sanitize( $str );

      $this->triggerHook( 'parse_before', $str );

      $quotes = array();
      $p = 0;
      $c = 0;
      while( $p = strpos( $str, '"', $p ) ){
        $p2               = strpos( $str, '"', $p+1 );
        $token            = '{{'.(++$c).'}}';
        $quotes[ $token ] = substr( $str, $p+1, $p2-$p-1 );
        $str              = substr( $str, 0, $p ) . $token . substr( $str, $p2+1 );
      }

      $arr = explode( ' ', $str );
      $ambiguous  = array();
      $strict     = array();
      $keys = array_keys( $quotes );
      foreach( $arr as $v ){
        if( strpos( $v, ':' ) !== false ){
          $a = explode(':', $v);

          if( !isset( $strict[ $a[0] ] ) ){
            $strict[ $a[0] ] = array();
          }

          $b = explode(',', $a[1]);
          for ($i=0; $i<count($b); ++$i) {
            $b[$i] = str_ireplace( $keys, $quotes, $b[$i] );
          }
          if( count( $b ) > 0 ){
            $c = count( $strict[ $a[0] ] );
            $strict[ $a[0] ][ $c ] = array();
            for( $i=0; $i<count($b); ++$i ){
              $strict[ $a[0] ][ $c ][] = trim( $b[ $i ] );
            }
          }
        } else {
          $ambiguous[] = $v;
        }
      }

      $result = array(
        'ambiguous' => $ambiguous,
        'strict'    => $strict
      );

      $this->triggerHook( 'parse_after', $result );
      $this->_lastParse = $result;

      return $result;
    }

	  public function sanitize( $str ){

      $this->triggerHook( 'sanitize_before', $str );

		  $str 	= str_replace("\n", " ", $str);
		  $str 	= trim( preg_replace('/\s\s+/', ' ', $str) );
      $str  = str_replace( array("'", "`", "\\"), '', $str );

      // check for uneven number of "
      if( count(explode('"', $str )) % 2 == 0 ){
        $str = preg_replace('/^(.*)"([^"]*)$/', '$1$2', $str);
      }

		  $str = preg_replace('/\s*(:\s*)+/', '$1', $str);
		  $str = preg_replace('/:"\s*([^"]*)\s*"\s*/', ':"$1" ', $str);
		  $str = preg_replace('/\b([^: ]+:([^: ]+|"[^: ]*")):+/', "$1 ", $str);
		  $str = preg_replace('/[: ]+([^: ]+:[^: ]+)\b/', " $1", $str);
      $str = trim( $str );

      $this->triggerHook( 'sanitize_after', $str );
      $this->_lastSanitize = $str;

      return $str;
	  }

	  public function getLastParse(){
      return $this->_lastParse;
	  }

	  public function getLastSanitize(){
      return $this->_lastSanitize;
	  }

	  private function triggerHook( $hookName, &$arg1, &$arg2 = null, &$arg3 = null ){
      if( isset( $this->hooks[ $hookName ] ) ){
        foreach( $this->hooks[ $hookName ] as $k => $function ){
          $function( $this, $arg1, $arg2, $arg3 );
        }
      }
      return $this;
	  }
	  public function setHookData( $name, array $data ){
      $this->hookData[ $name ] = $data;
      return $this;
	  }
	  public function getHookData( $name ){
      if( isset( $this->hookData[ $name ] ) ){
        return $this->hookData[ $name ];
      }
      return null;
	  }
	  public function addHook( $hookName, $name, $function ){
      if( !isset( $this->hooks[ $hookName ] ) ){
        $this->hooks[ $hookName ] = array();
      }
      $this->hooks[ $hookName ][ $name ] = $function;
      return $this;
	  }
    public function removeHook( $hookName, $name ){
      if( isset( $this->hooks[ $hookName ] ) && isset( $this->hooks[ $hookName ][ $name ] ) ){
        unset( $this->hooks[ $hookName ][ $name ] );
      }
      return $this;
    }
	  public function getHooks(){
      return $this->hooks;
	  }

	  public function setFields( array $val ){
	    $this->fields = $val;
	    return $this;
	  }
	  public function getFields(){
	    return $this->fields;
	  }

  }

?>
