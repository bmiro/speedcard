<?php

/**
 * Add a JS to this page.
 */
function add_js($filepath, $weight = 0) {
  global $tpl_js;
  $tpl_js[$filepath] = $weight;
}

/**
 * Add a CSS to this page.
 */
function add_css($filepath, $weight = 0) {
  global $tpl_css;
  $tpl_css[$filepath] = $weight;
}

/**
 * Set page title to show in 'title' head HTML.
 */
function page_set_title($title) {
  global $page_title;
  $page_title = $title;
}

/**
 * Get page title to show in 'title' head HTML.
 */
function page_get_title() {
  global $page_title;
  return $page_title;
}
