#WTherm

This is a second rewritten version of my [original WTherm project](https://github.com/NiekProductions/WTherm-alpha/). This project was expanded as part of a final thesis which can be found in the **report** folder. The new WTherm has added functionality like scheduling, and uses a thermal model to predict the temperature. If you set the schedule to 20.0 degrees at 6:00, it will make sure that it reaches 20 degrees at exactly that time.

## Installation instructions
 - Download Raspbian for BananaPi from http://www.lemaker.org/resources/9-81/raspbian_for_bananapi.html
 - Write it to an SD card and boot from the SD card
 - Follow the instructions on http://banoffeepiserver.com/setup-raspbian-on-a-sata-hard-disk.html to install Raspbian on the SATA Hard Disk
 - *(optional - necessary for outside access)* Configure the BananaPi to have a static IP address and setup port forwarding on the router
 - Perform a `sudo apt-get update`
 - Install a LEMP (Linux-Nginx-MySQL-PHP) stack by following this tutorial: https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-debian-7
 - Edit `sudo nano /etc/nginx/sites-available/default` and change `root` to `/usr/local/bin/WTherm/www`.
 - Edit `/etc/php5/fpm/php.ini` and change `short_open_tag` to `On`.
 - Restart php5-fpm: `sudo service php5-fpm restart`
 - Also install php5-curl and command line PHP:
```bash
sudo apt-get install php5-curl php5-cli
sudo service nginx restart
```
 - `cd` into your home directory: `cd ~`
 -  Clone the git repo: `git clone https://github.com/niekproductions/WTherm-alpha.git`.
 -  Create a symlink to it: `cd /usr/local/bin && sudo ln -s ~/WTherm/code WTherm`.
 -  Import WTherm.sql into the MySQL server.
 -  Edit the `base.php` configuration to suit your installation.
 -  Add a new group of users that are allowed to control GPIO and add `www-data` to the group:
```bash
sudo groupadd GPIOcontrol
sudo usermod -a -G GPIOcontrol www-data
```
 -  Allow the group to use the GPIO utility by adding the following line to the end of `/etc/sudoers`:
`%GPIOcontrol ALL=NOPASSWD: /usr/local/bin/gpio`
 -  Use `crontab -e` and add these lines to the end of the file to run the thermostat script every 5 minutes:
```
*/5 * * * * php5 /usr/local/bin/WTherm/thermostat.php >> /usr/local/bin/WTherm/wtherm.log
@reboot php5 /usr/local/bin/WTherm/startup.php >> /usr/local/bin/WTherm/wtherm.log &
```
 -  And finally, add a user by executing the following command:
`php5 /usr/local/bin/WTherm/adduser.php [username] [password] [password-confirm]`
You should now be able to log into the WTherm!

**optional**
 -  Set up nightly database backups by creating backup.sh:
```bash
#!/bin/bash
NOW=$(date +"%d-%m-%Y")

MUSER="WTherm"
MPASS="[database password]"
DB="wtherm"

FILE="$HOME/backup/wtherm-$NOW.gz"

/usr/bin/mysqldump -u $MUSER -p$MPASS $DB | /bin/gzip -9 > $FILE
```
 -  Mark it as an executable script: `chmod +x backup.sh`
 -  Add it to the crontab (`crontab -e`):
```bash
30 2 * * * ~/backup.sh
```

#License
The code for this project was released under the **MIT License** (MIT)

Copyright (c) 2015 Niek Blankers

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

Everything in the report folder was released under the **Creative Commons Attribution-NonCommercial-ShareAlike 4.0** International License. To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-sa/4.0/