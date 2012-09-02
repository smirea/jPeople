<?php

class LdapConnection {
  private $_username;
  private $_password;
  private $_hostname;
  private $_port;
  private $_basedn;
  private $_con;

  public function __construct($username, $password, $hostname, $basedn = null) {
    $this->_username = $username;
    $this->_password = $password;
    $this->_hostname = $hostname;
    $this->_port = 389;
    $this->_basedn = $basedn;
    $this->_con = null;
  }

  public function __destruct() {
    if (null != $this->_con) {
      ldap_unbind($this->_con);
    }
  }

  /**
   * Executes a query and creates a connection if never used before.
   * 
   * @param $dn the dn to use for the query
   * @param $filter the filter to use for the query
   * @param $attributes the attributes to get back from the query
   * 
   * @return result array, null for search error, exception for connection
   */
  public function query($dn = null, $filter = null, $attributes = null) {
    if (null == $this->_con) {
      $this->_con = ldap_connect($this->_hostname, $this->_port);
      if (!$this->_con) {
        throw new Exception("Could not connect!");
        //return null;
      } else {
        if(!ldap_bind($this->_con, $this->_username, $this->_password)) {
          throw new Exception("Could not bind!");
          //return null;
        }
      }
    }

    if (null == $dn) {
      if (null == $this->_basedn) {
        $dn = "";
      } else {
        $dn = $this->_basedn;
      }
    }

    if (null == $filter) {
      $filter = "objectClass=*";
    }

    if (null == $attributes) {
      $attributes = array();
    }

    $search = ldap_search($this->_con, $dn, $filter, $attributes);
    $result = array();

    if (FALSE === $search) {
      return null;
    } else {
      $result = ldap_get_entries($this->_con, $search);
    }

    return $result;
  }
}

?>
