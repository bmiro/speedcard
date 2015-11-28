<?php

/**
 * Returns the translated version of a string.
 * If args are specified, substitute them.
 */
function t($string, $args = 0, $other_lang = NULL) {
  global $lang;

  if (($lang == 'en' || $string == '') && ($other_lang == NULL || $other_lang == 'en')) {
    if (!$args) {
      return $string;
    }
    else {
      return strtr($string, $args);
    }
  }
  else { // $lang needs to be translated.
    if (!$args) {
      return _t($string, $other_lang);
    }
    else {
      return strtr(_t($string, $other_lang), $args);
    }
  }
}

/**
 * If you set the $valid_langs array, the framework assumes you have some item
 * (so a 'count' call is not needed) and that gettext is installed.
 */
if (isset($valid_langs) && is_array($valid_langs)) {

  /**
   * Translate string from code (usually English) to $lang.
   */
  function _t($string, $other_lang) {
    global $lang;
    global $lang_init;
  
    if ($other_lang) {
      lang_init();
      $lang_init = $other_lang;
    }
    else if ($lang_init !== $lang) {
      lang_init();
      $lang_init = $lang;
    }
  
    return gettext($string);
  }

  /**
   * Initializes the gettext layout.
   */
  function lang_init() {
    global $lang;
    global $locale_dir;
    global $valid_langs;
  
    setlocale(LC_ALL, $valid_langs[$lang]);
  
    $domain = 'lemur';
    bindtextdomain($domain, $locale_dir);
    textdomain($domain);
  }
}
else {

  /**
   * Dummy _t() function to make t() work even if gettext is not installed.
   */
  function _t($string, $other_lang) {
    return $string;   
  }
}

