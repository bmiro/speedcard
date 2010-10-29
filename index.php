<?php

/**
 * Copyright (c) 2009-2010 Antoni Villalonga, http://friki.cat/
 * Copyright (c) 2009-2010 Javier Linares, http://javierlinares.com/
 * Copyright (c) 2009-2010 Paulo Oliveira, http://paulooliveira.net/
 *
 * This file is part of Lemur Framework, http://lemurframework.com/
 *
 * Lemur Framework is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, version 3 of the
 * License.
  *
 * Lemur Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Lemur Framework. If not, see <http://www.gnu.org/licenses/>.
 */

include 'core/bootstrap.inc.php';

$json = FALSE;
$url  = $_GET['q'];

if ($devel) {
  // Debugging.
  $t_query = 0;
  $t_mem   = 0;
  $mem_h   = 0;
  $mem_m   = 0;
  $t_start = microtime(TRUE);
  $t_tpl_start = NULL;
  $t_app_start = 0;
}

// Call the controller.
if (list($controller, $args) = dispatcher_choose($url)) {
  include $path_app . '/' . $controller;
  $t_app_start = microtime(TRUE);

  $controller_output = controller($args);
  print $controller_output;
}
else {
  $t_app_start = microtime(TRUE);
  print page_not_found($url);
}

// Debugging for development mode.
if ($devel && $json === FALSE) {
  $t_end = microtime(TRUE);
  $t_app = (int)(round($t_app_start - $t_start, 4)*10000);
  $t_tpl = (($t_tpl !== NULL) ? ((int)(round($t_tpl_start - $t_start, 4)*10000)) : 0);
  $t_all = (int)(round($t_end - $t_start, 4)*10000);
  $t_qry = (int)(round($t_query, 4)*10000);
  $t_mem = (int)(round($t_mem, 4)*10000);

  print "\n<!-- $site_name (";
  print 'db '. $db->count ."q = ${t_qry}s ; ";   // Database:  Number of queries and time used.
  print "mc {$mem_h}h+{$mem_m}m = ${t_mem}s ; "; // memcached: Hits, misses and time used.
  print 'loves caching @ ' . date('Y/m/d H:i:s') . 'GMT -->';
}
