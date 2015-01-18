#WTherm

This is a second rewritten version of my [original WTherm project](https://github.com/NiekProductions/WTherm-alpha/). It adds functionality like scheduling, and uses a thermal model to predict the temperature. If you set the schedule to 20.0 degrees at 6:00, it will make sure that it reaches 20 degrees at exactly that time.

## Installation instructions
1. Download Raspbian for BananaPi from http://www.lemaker.org/resources/9-81/raspbian_for_bananapi.html
2. Write it to an SD card and boot from the SD card
3. Follow the instructions on http://banoffeepiserver.com/setup-raspbian-on-a-sata-hard-disk.html to install Raspbian on the SATA Hard Disk
4. *(optional - necessary for outside access)* Configure the BananaPi to have a static IP address and setup port forwarding on the router
6. Perform a `sudo apt-get update`
7. Install a LEMP (Linux-Nginx-MySQL-PHP) stack by following this tutorial: https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-debian-7
8. Edit `sudo nano /etc/nginx/sites-available/default` and change `root` to `/usr/local/bin/WTherm/www`.
9. Also install php5-curl and command line PHP:
```bash
sudo apt-get install php5-curl php5-cli
sudo service nginx restart
```
9. `cd` into your home directory: `cd ~`
10. Clone the git repo: `git clone https://github.com/niekproductions/WTherm-alpha.git`.
11. Create a symlink to it: `cd /usr/local/bin && sudo ln -s ~/WTherm/code WTherm`.
12. Import WTherm.sql into the MySQL server.
13. Edit the `base.php` configuration to suit your installation.
14. Add a new group of users that are allowed to control GPIO and add `www-data` to the group:
```bash
sudo groupadd GPIOcontrol
sudo usermod -a -G GPIOcontrol www-data
```
15. Allow the group to use the GPIO utility by adding the following line to the end of `/etc/sudoers`:
`%GPIOcontrol ALL=NOPASSWD: /usr/local/bin/gpio`
16. Use `crontab -e` and add these lines to the end of the file to run the thermostat script every 5 minutes:
```
*/5 * * * * php5 /usr/local/bin/WTherm/thermostat.php >> /usr/local/bin/WTherm/wtherm.log
@reboot php5 /usr/local/bin/WTherm/startup.php >> /usr/local/bin/WTherm/wtherm.log &
```
17. And finally, add a user by executing the following command:
`php5 /usr/local/bin/WTherm/adduser.php [username] [password] [password-confirm]`
You should now be able to log into the WTherm!

#License
This project was released under the MIT License (MIT)

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
