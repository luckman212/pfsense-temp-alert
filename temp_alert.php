#!/usr/bin/env php
<?php

  require_once("notices.inc");
  require_once("util.inc");

  $alarm_temp = 47;
  $sentinel = '/tmp/temp_alarm';

  function do_notify($temp, $alarm) {
    global $alarm_temp, $sentinel, $sensor;
    $msg = null;
    $alarm = boolval($alarm);
    switch ($alarm) {
      case false:
        if (file_exists($sentinel)) {
          $msg = "Temperature of {$sensor} has returned to normal ({$temp}C)";
          unlink($sentinel);
        }
        break;
      case true:
        if (!file_exists($sentinel)) {
          $msg = "Temperature of {$sensor} ({$temp}C) exceeded alarm threshold ({$alarm_temp}C)";
          $fh = fopen($sentinel, 'w');
          fwrite($fh, "{$sensor}:{$t}");
          fclose($fh);
        }
        break;
      default:
        break;
    }
    if (!empty($msg)) {
      log_error($msg);
      notify_via_smtp($msg . "\n\n\n");
    }
  }

  // discover sensors and auto-select the hottest one  
  $sensor = trim($argv[1]);
  if (strlen($sensor) == 0) {
    exec('sysctl -a | grep temperature | awk -F: \'{sub("C","",$2); if($2>max_t){max_t=$2;v=$1}} END {print v}\'', $output, $retval);
    $sensor = $output[0];
    unset($output);
  }

  // check temp
  exec("sysctl -n " . escapeshellarg($sensor), $output, $retval);
  if (($retval == 0) && (count($output))) {
    $temp = $output[0] ? intval($output[0]) : -1;
    do_notify($temp, $temp >= $alarm_temp);
  }

?>
