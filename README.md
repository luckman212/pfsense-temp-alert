![icon](hot_firewall.png)

# pfsense-temp-alert

### Abstract

This small script will monitor thermals using the hottest sensor on your system (or you can pass a parameter from sysctl to use a specific sensor, e.g. `dev.cpu.0.temperature`). If the temp reaches or exceeds `$alarm_temp`, an email notification will be dispatched. If or when temps return to normal, you will be notified of that event as well.

### Setup

1. scp the script to `/root/bin/`
2. `chmod +x /root/bin/temp_alert.php`
3. add cronjob[^1] (suggest every 5m): `/usr/bin/nice -n20 /root/bin/temp_alert.php`
4. adjust `$alarm_temp` as needed

### References

- https://docs.netgate.com/pfsense/en/latest/monitoring/status/hardware.html
- https://www.reddit.com/r/PFSENSE/comments/x4nadi/pfsensetempalert_simple_php_script_to_send_alerts/

[^1]: _Install the Cron package from System → Packages if you don't already have it._
