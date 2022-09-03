# pfsense-temp-alert

### Abstract

This small script will monitor thermals using the hottest sensor on your system (or you can pass a parameter from sysctl to use a specific sensor, e.g. `dev.cpu.0.temperature`). If the temp exceeds `$alarm_temp` an email notification will be dispatched. If or when temps return to normal, you will be notified of that event as well.

### Setup

1. scp the script to `/root/bin/`
2. `chmod +x /root/bin/temp_alert.php`
3. add cronjob (suggest every 5m): `/usr/bin/nice -n20 /root/bin/temp_alert.php`
4. adjust `$alarm_temp` as needed
