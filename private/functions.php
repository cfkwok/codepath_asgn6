<?php

  function h($string="") {
    return htmlspecialchars($string);
  }

  function u($string="") {
    return urlencode($string);
  }

  function raw_u($string="") {
    return rawurlencode($string);
  }

  function redirect_to($location) {
    header("Location: " . $location);
    exit;
  }

  function url_for($script_path) {
    return DOC_ROOT . $script_path;
  }

  function is_post_request() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
  }

  function is_get_request() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
  }

  function request_is_same_domain() {
    if(!isset($_SERVER['HTTP_REFERER'])) { return false; }
    $referer_host = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
    return ($referer_host === $_SERVER['HTTP_HOST']);
  }

  function display_errors($errors=array()) {
    $output = '';
    if (!empty($errors)) {
      $output .= "<div class=\"errors\">";
      $output .= "Please fix the following errors:";
      $output .= "<ul>";
      foreach ($errors as $error) {
        $output .= "<li>{$error}</li>";
      }
      $output .= "</ul>";
      $output .= "</div>";
    }
    return $output;
  }

  function record_failed_login($username) {
    $sql_date = date("Y-m-d H:i:s");
    
    $fl_result = find_failed_login($username);
    $failed_login = db_fetch_assoc($fl_result);

    if (!$failed_login) {
      $failed_login = [
        'username' => $username,
        'count' => 1,
        'last_attempt' => $sql_date
      ];
      insert_failed_login($failed_login);
    } else {
      $failed_login['count'] = $failed_login['count'] + 1;
      $failed_login['last_attempt'] = $sql_date;
      update_failed_login($failed_login);
    }

    return true;
  }

  function throttle_time($username) {
    $threshold = 5;
    $lockout = 60 * 5;
    $fl_result = find_failed_login($username);
    $failed_login = db_fetch_assoc($fl_result);
    if (!isset($failed_login)) { return 0; }
    if ($failed_login['count'] < $threshold) { return 0; }
    $last_attempt = strtotime($failed_login['last_attempt']);
    $since_last_attempt = time() - $last_attempt;
    $remaining_lockout = $lockout - $since_last_attempt;

    if ($remaining_lockout < 0) {
      reset_failed_login($username);
      return 0;
    } else {
      return $remaining_lockout;
    }
  }

  function reset_failed_login($username) {
    $fl_result = find_failed_login($username);
    $failed_login = db_fetch_assoc($fl_result);
    if (isset($failed_login)) {
      $failed_login['count'] = 0;
      update_failed_login($failed_login);
    }
  }

?>
