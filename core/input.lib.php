<?php

/**
 * Check and validate user input.
 */
class Input {

  /**
   * Sanitize input. Controller should call this function if they are going
   * to read data from $_GET, $_POST and/or $_COOKIES.
   *
   * FIXME: Think about this.
   * FIXME: Write this when debate is over.
   */
  static public function sanitize($arg) {
    return TRUE;
  }

  /**
   * Validate an email address.
   */
  static public function validMail($mail) {
    $valid = FALSE;

    if (preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $mail)) {
      $valid = TRUE;
    }

    return $valid;
  }

  /**
   * Check invalid chars.
   */
  static public function checkInvalidChars($username) {
    mb_internal_encoding('UTF-8');

    $len = mb_strlen($username);
    for($i = 0; $i < $len; $i++) {
      $char = mb_substr($username, $i, 1);
      if (preg_match('"[^a-zA-Z0-9]"', $char)) {
        $errors[] = $char;
      }
    }

    if (is_array($errors)) {
      return mb_ereg_replace('\ ', '&lt;SPACE&gt;', implode(',', array_unique($errors)));
    }
    return FALSE;
  }

}
