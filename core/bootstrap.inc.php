<?php

define('NL', "\n");

/**
 * 'core' bootstrap.
 */
include 'core/misc.inc.php';
include 'core/theme.class.php';
include 'core/theme.inc.php';
include 'core/input.lib.php';
include 'core/form.class.php';

/**
 * Define domain.
 */
if (isset($_SERVER['HTTP_HOST'])) {
  define('DOMAIN', $_SERVER['HTTP_HOST']);
}
else {
  die('ERROR: HTTP_HOST not set. Impossible to load settings.');
}

/**
 * It is *mandatory* that you create that file for any domain you point to
 * your Lemur Framework copy of the code, as we do not want to make a
 * file_exists. It just only needs to exist and it might be a symlink.
 */
require 'sites/common/settings.php';
require 'sites/' . DOMAIN . '/settings.php';

/**
 * 'core' bootstrap that with settings dependency.
 */
include 'core/locale.inc.php';

/**
 * Process settings.
 */
if (!isset($path_app) || $path_app == '') {
  $path_app = 'app'; // Default path for controllers.
}
if (!isset($path_class) || $path_class == '') {
  $path_class = 'class'; // Default path for classes.
}

/**
 * Load extra classes if needed.
 */
if (isset($db_name) && $db_name != '') {
  include 'extra/database.class.php';
  $db = new Database();
}
if (isset($memcache_servers) && is_array($memcache_servers)) {
  include 'extra/mem.inc.php';
}

/**
 * Selects the controller to use based on the $url.
 * - Performance-sensitive pages should go first.
 * - For subdispatchers, selection can be delegated to a function.
 */
function dispatcher_choose($url) {
  global $dispatcher_conf;

  if (is_array($dispatcher_conf)) {
    $dispatcher_conf['@.*@'] = NULL;
  }
  else {
    $dispatcher_conf = array(
      '@.*@' => NULL,
    );
  }

  foreach ($dispatcher_conf as $regexp => $controller) {
    if (preg_match($regexp, $url, $matches)) {
      return array($controller, $matches);
    }
  }
}
