#!/usr/bin/env php
<?php

  require_once("notices.inc");
  require_once("util.inc");

  // debugging
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
  ini_set("log_errors", 1);
  ini_set("error_log", "/tmp/temp_alert.log");

  error_log("=== script begin ===");
  $alarm_temp = 58;
  $sentinel = '/tmp/temp_alarm';

  function do_notify($temp, $alarm) {
    global $alarm_temp, $sentinel, $sensor;
    error_log("in do_notify(): alarm_temp={$alarm_temp} temp={$temp} alarm={$alarm}");
    $alarm = boolval($alarm);
    switch ($alarm) {
      case false:
        if (file_exists($sentinel)) {
          error_log("alarm state changing from true to false, notification will be sent");
          $msg = "Temperature of {$sensor} has returned to normal ({$temp}C)";
          unlink($sentinel);
        } else {
          error_log("alarm state is false; everything is OK");
        }
        break;
      case true:
        if (!file_exists($sentinel)) {
          error_log("alarm state changing from false to true, notification will be sent");
          $msg = "Temperature of {$sensor} ({$temp}C) exceeded alarm threshold ({$alarm_temp}C)";
          $fh = fopen($sentinel, 'w');
          fwrite($fh, "{$sensor}:{$temp}");
          fclose($fh);
        } else {
          error_log("alarm state is true but notification was already sent (sentinel exists)");
        }
        break;
      default:
        error_log("alarm boolval in an unknown state");
        break;
    }
    if (!empty($msg)) {
      error_log("sending notification via smtp: {$msg}");
      log_error($msg);  //goes to system.log
      notify_via_smtp($msg . "\n\n\n");
    }
  }

  // discover sensors and auto-select the hottest one  
  $sensor = isset($argv[1]) ? trim($argv[1]) : null;
  if (strlen($sensor) == 0) {
    error_log("no sensor specified, trying to auto-detect");
    exec('sysctl -a | grep temperature | awk -F: \'{sub("C","",$2); if($2>max_t){max_t=$2;v=$1}} END {print v}\'', $output, $retval);
    $sensor = $output[0];
    unset($output);
    if (empty($sensor)) {
      error_log("failed to auto-detect a temperature sensor");
      exit();
    }
  } else {
    error_log("using sensor {$sensor} supplied at the commandline");
  }

  // check temp
  exec("sysctl -n " . escapeshellarg($sensor), $output, $retval);
  if (($retval == 0) && (count($output))) {
    $temp = $output[0] ? intval($output[0]) : -1;
    $alarm = ($temp >= $alarm_temp) ? 1 : 0;
    error_log("temp: {$temp} alarm_temp: {$alarm_temp} alarm state: {$alarm}");
    do_notify($temp, $alarm);
  } else {
    error_log("sensor {$sensor} returned no data");
  }

  error_log("=== script end ===" . "\n");

?>
