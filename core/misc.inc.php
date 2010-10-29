<?php

/**
 * Return a Not Found page.
 */
function page_not_found($url = NULL) {
  $tpl = new Template();
  $tpl->add('main', 'common/404'); // FIXME: Does it exist?.
  $tpl->setTPL('page');
  // FIXME: What about TTLs.

  header('HTTP/1.0 404 Not Found');
  return $tpl->render();
}

/**
 * Return a standard page with a message.
 *
 * This is useful to show a message to the user, such as 'you have been logged
 * out' or 'you are now a registered user'. The $store param is really
 * important, if the message contains personal data it will always have a
 * FALSE.
 */
function standard_page($message, $store = FALSE) {
  $tpl = new Template();
  $tpl->add('main', 'blocks/echo', array('data' => $message));
  $tpl->setTPL('page');
  // FIXME: What about TTLs.
  return $tpl->render();
}

/**
 * Returns an access denied page.
 */
function access_denied() {
  $tpl = new Template();
  $tpl->add('main', 'common/503'); // FIXME: Does it exist?.
  $tpl->setTPL('page');
  // FIXME: What about TTLs.
  return $tpl->render();
}

/**
 * Send mail general function.
 */
function send_mail($from, $to, $subject, $message, $bcc = NULL, $reply_to = NULL, $html = FALSE, $extra_headers = '', $log = TRUE) {
  global $db;
  global $base_url;

  $headers  = "From: $from\r\n";
  $headers .= ($bcc ? 'Bcc: ' .$bcc. "\r\n" : '');
  $headers .= ($reply_to ? 'Reply-To: '.$reply_to . "\r\n" : '');
  $headers .= "X-Mailer: motogp.com\r\n";

  if ($html) {
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=utf-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
  }
  else {
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
  }
  $headers .= $extra_headers;

  $ret = mail($to, $subject, $message, $headers);

  if (isset($base_url) && $base_url != '' && $log == TRUE && $db) {
    $from    = $db->cleanString($from);
    $to      = $db->cleanString($to);
    $subject = $db->cleanString($subject);
    $message = $db->cleanString($message);

    if ($bcc && $db) {
      $bcc = $db->cleanString($bcc);
    }
    if ($reply_to && $db) {
      $reply_to = $db->cleanString($reply_to);
    }

    if ($html && !is_bool($html)) {
      return 'SQL injection on HTML var: '.$html;
    }

    if ($extra_headers != '' && $db) {
      $extra_headers = $db->cleanString($extra_headers);
    }

    $backtrace = '';
    if ($ret === FALSE) {
      $backtrace = str_replace("'","\'",pr2(debug_backtrace(),TRUE));
    }

    $sql  = 'INSERT INTO send_mail_log (date,m_from,m_to,result,body,bcc,reply_to,html';
    $sql .= ",extra_headers,subject,backtrace) VALUES (NOW(),'$from','$to','$ret','$message','";
    $sql .= ($bcc ? $bcc : '');
    $sql .= '\',\'';
    $sql .= ($reply_to ? $reply_to : '');
    $sql .= '\',\'';
    $sql .= ($html ? 'TRUE' : 'FALSE');
    $sql .= "','$extra_headers','$subject','$backtrace')";
    $db->master($sql);
  }

  $clean_subject = str_replace("\n", ' ', $subject);

  if ($ret === TRUE) {
    $result = 'TRUE';
  }
  else {
    $result = 'FALSE';
  }
  $log_line = date('d/m/Y H:i:s') .' - '. $result .' - '. $from .' - '. $to .' - '. $clean_subject ."\n";
  file_put_contents('/tmp/send_mail.log', $log_line, FILE_APPEND);
  return $ret;
}

/**
 * Strip slashes.
 */
function stripslashes_deep($value) {
  return (is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value));
}

/**
 * Send the user to a different page.
 */
function goto_url($path = '', $http_response_code = 302) {
  header('Location: '. $path, TRUE, $http_response_code);
  exit();
}

/**
 * Write a message in a log file in the default $path_log.
 */
function logger($message, $file) {
  global $path_log;

  if (!is_dir($path_log)) {
    // Be sure log path is ready.
    if (!mkdir($path_log, 0600, TRUE)) {
      return FALSE;
    }
  }

  $now       = time();
  $now_human = date('Y/m/d H:i:s', $now);
  $message   = "$now - $now_human - $message";
  $message   = str_replace(array("\n", "\r"), ' ', $message);

  if ($fp = fopen($path_log . '/' . $file, 'a')) {
    fwrite($fp, $message."\n");
    fclose($fp);
  }
}

/**
 * Nicer print_r for development or logging.
 * If $arg is FALSE or not set, prints $val.
 * If $arg is TRUE, return $val.
 */
function pr2($val, $arg = FALSE){
  if ($arg) {
    return '<pre>' . print_r($val, TRUE) . '</pre>';
  }
  print '<pre>';
  print_r($val);
  print '</pre>';
}
