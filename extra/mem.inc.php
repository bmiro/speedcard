<?php

/**
 * Init memcached object and connection.
 *
 * Support for 'Memcache' (and not 'Memcached') in comments.
 */
function mem_init() {
  global $memcache;
  global $memcache_servers;
  global $memprefix;
  global $meminit;

  // Only if not done previously.
  if (!isset($meminit)) {
    $meminit  = TRUE;

    // Memcache
    // $memcache = new Memcache;

    // Memcached
    $memcache = new Memcached;

    if (is_array($memcache_servers)) {
      foreach ($memcache_servers as $server) {
        $memcache->addServer($server[0], $server[1]);
      }
    }
  }
}

/**
 * Get $id from memcache.
 */
function mem_get($id) {
  global $memcache;
  global $memprefix;
  global $domain;
  global $t_mem;
  global $t_start;
  global $mem_m;
  global $mem_h;

  if ($id == '') {
    return FALSE;
  }

  $t_start = microtime(TRUE);

  mem_init();
  if (!$memcache) {
    return FALSE;
  }

  $result = $memcache->get($memprefix.$domain.$id);
  ($result === FALSE) ? $mem_m++ : $mem_h++;
  $t_mem = microtime(TRUE) - $t_start;
  return $result;
}

/**
 * Set $id with $content to return during $ttl seconds.
 *
 * WARNING: $ttl = 0 implies KEEP FOREVER (actually until memcached
 * decides to remove it or is rebooted).
 */
function mem_set($id, $content, $ttl = 900) {
  global $memcache;
  global $memprefix;
  global $domain;
  global $t_mem;
  global $t_start;

  if ($id == '') {
    return FALSE;
  }

  if (strlen($id) > 255) {
    $logmsg  = 'ERROR - ';
    $logmsg .= 'strlen(ID) = '. strlen($id) .' - ';
    $logmsg .= 'ID: '. $id;
    $logmsg .= "\n";
    logger($logmsg, 'memcache.log');
    return FALSE;
  }

  mem_init();
  if (!$memcache) {
    return FALSE;
  }

  // // Memcache
  // $result = $memcache->set($memprefix.$domain.$id, $content, FALSE, $ttl);

  // Memcached
  $result = $memcache->set($memprefix.$domain.$id, $content, $ttl);

  if ($result == FALSE) {
    $logmsg  = 'ERROR - Set operation - ';
    $logmsg .= 'strlen(ID) :'. strlen($id);
    $logmsg .= ' - ID: ' . $id;
    $logmsg .= "\n";
    logger($logmsg, 'memcache.log');
  }

  $t_mem = microtime(TRUE) - $t_start;
  return $result;
}

/**
 * Removes $id key in memcached.
 */
function mem_delete($id, $domain_force = '') {
  global $memcache;
  global $memprefix;
  global $domain;
  global $t_mem;
  global $t_start;

  mem_init();
  if (!$memcache) {
    return FALSE;
  }

  $domain_use = $domain;
  if ($domain_force != '') {
    $domain_use = $domain_force;
  }

  $result = $memcache->delete($memprefix.$domain_use.$id);
  $t_mem  = microtime(TRUE) - $t_start;
  return $result;
}
