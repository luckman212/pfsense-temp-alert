#!/usr/bin/env php
<?php

  require_once("notices.inc");
  require_once("util.inc");

  $alarm_temp = 47;
  $sentinel = '/tmp/temp_alarm';
  $sensor = trim($argv[1]);

  function do_notify($temp, $status) {
    global $alarm_temp, $sentinel, $sensor;
    $msg = null;
    switch ($status) {
      case 'green':
        if (file_exists($sentinel)) {
          $msg = "Temperature of {$sensor} has returned to normal ({$temp}C)";
          unlink($sentinel);
        }
        break;
      case 'red':
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

  // check temp
  if (strlen($sensor) == 0) {
    exec('sysctl -a | grep temperature | awk -F: \'{sub("C","",$2); if($2>max_t){max_t=$2;v=$1}} END {print v}\'', $output, $retval);
    $sensor = $output[0];
    unset($output);
  }
  exec("sysctl -n " . escapeshellarg($sensor), $output, $retval);
  if (($retval == 0) && (count($output))) {
    $temp = $output[0] ? intval($output[0]) : -1;
    if ($temp >= $alarm_temp) {
      do_notify($temp, 'red');
    } else {
      do_notify($temp, 'green');
    }
  }

?>
