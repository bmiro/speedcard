<?php

/**
 * Database abstraction layer.
 * Implementation for MySQL.
 */
class Database {

  /**
   * Actually perform the query.
   * $type can be 'master' or 'slave'.
   * If $query is FALSE, escape string in $string.
   */
  private function query($type, $query, $string = '') {
    global $url;
    global $db_name;
    global $db_user;
    global $db_pass;

    global $t_query;
    $t_query_start = microtime(TRUE);

    // Check if we have 'queries credit'.
    if ($this->no_more !== FALSE) {
      return FALSE;
    }

    // Debugging.
    if ($query !== FALSE) {
      $this->count += 1;
      $query_log  = preg_replace('/\n/', ' ', $query);
      $query_log  = preg_replace('/ +/', ' ', $query_log);
      $this->log .= $query_log."\n";
    }

    // If needed, connect to database.
    if (empty($this->{$type.'_handler'})) {
      if ($type == 'master') {
         global $db_host_master;
        $host = $db_host_master;
      }
      else if ($type == 'slave') {
         global $db_host_slave;
        $host = $db_host_slave;
      }
      $this->{$type.'_handler'} = mysqli_connect($host, $db_user, $db_pass, $db_name);

      if ($this->{$type.'_handler'}->connect_error) {
        // Error in connection: Report to sysadmins.
        global $base_url;
        logger("$base_url/$url - Error trying to connect to $type", 'db_query_error.log');

        // Error in connection: Report to user.
        header('HTTP/1.1 503 Service Unavailable');
        $tpl = new Template();
        print $tpl->error('temporarily');
        die();
      }
      else {
        // Connection OK.
        mysqli_query($this->{$type.'_handler'}, 'SET NAMES "utf8"');
      }
    }

    // Make the query.
    if ($query !== FALSE) {
      $result = mysqli_query($this->{$type.'_handler'}, $query);
      if ($result === FALSE) {
        // Log query errors in a file.
        $string = "$url - ". mysqli_error($this->{$type.'_handler'}) ." - $query";
        logger($string, 'db_query_error.log');
      }
      $t_query += microtime(TRUE) - $t_query_start;
      return $result;
    }
    else {
      // Not an actual query.
      return mysqli_real_escape_string($this->{$type.'_handler'}, $string);
    }
  }

  /**
   * Performs a query in the master.
   */
  public function master($query) {
    return $this->query('master', $query);
  }

  /**
   * Performs a query in the slave.
   */
  public function slave($query) {
    return $this->query('slave', $query);
  }

  /**
   * Closes connection to database.
   * $type can be 'master' or 'slave'.
   */
  public function close($type) {
    $this->no_more = TRUE;

    if ($type == 'all') {
      if ($this->master_handler !== NULL) {
        mysqli_close($this->master_handler);
      }
      if ($this->slave_handler !== NULL) {
        mysqli_close($this->slave_handler);
      }
    }
    else if (isset($this->{$type.'_handler'}) && $this->{$type.'_handler'} !== NULL) {
      mysqli_close($this->{$type.'_handler'});
    }
  }

  /**
   * Fetch a row.
   */
  public function fetch($result) {
    if ($result === FALSE) {
      return FALSE;
    }
    return mysqli_fetch_array($result, MYSQL_ASSOC);
  }

  /**
   * Return the number of rows.
   */
  public function num($result) {
    if ($result === FALSE) {
      return FALSE;
    }
    return mysqli_num_rows($result);
  }

  /**
   * Return the last insert id.
   */
  public function lastInsertId() {
    return mysqli_insert_id($this->master_handler);
  }

  /**
   * Return the afected rows.
   */
  public function affectedRows() {
    return mysqli_affected_rows($this->master_handler);
  }

  /**
   * Clean a string using MySQL API.
   */
  public function cleanString($string) {
    return $this->query('slave', FALSE, $string);
  }

  // Class internal vars.
  private static $master_handler = FALSE;
  private static $slave_handler  = FALSE;
  private $no_more = FALSE;

  // Class debugging vars.
  public $count = 0;
  public $log   = '';

}
