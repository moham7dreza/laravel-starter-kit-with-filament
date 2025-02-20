# ******************************************************************** Dependencies ****************************************************************
sudo apt update
# 0. git
sudo apt install composer # install git composer and php8.2
git config --global user.name "admin"
git config --global user.email "admin@admin.com"
# 1. nginx
sudo apt install nginx
# 2. enable ubuntu firewall and allow http
sudo ufw enable
sudo ufw allow 'Nginx HTTP'
# 3. mysql setup if you in local skip validate password component
sudo apt install mysql-server
sudo mysql_secure_installation
# 4.1. php and dev tools and pecl
sudo apt install php-fpm php-mysql php-dev php-pear
# 4.2. php8.2
sudo apt install ca-certificates apt-transport-https software-properties-common lsb-release -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt upgrade
sudo apt install php8.2
# 4.2.2. php extensions and development tools
sudo apt install php8.2-{dev,pcov,xdebug,sqlite3,cli,soap,fpm,xml,curl,cgi,mysql,mysqlnd,gd,bz2,ldap,pgsql,opcache,zip,intl,common,bcmath,imagick,xmlrpc,readline,memcached,redis,mbstring,apcu,xml,dom,memcache,mongodb}
# enable php8.2
sudo systemctl enable php8.2-fpm
# 5. required extensions to be enabled in php.ini
sudo apt install php-pear
sudo pecl install mongodb excimer apcu
# 5.1 uncomment or add them in /etc/php/8.2/cli/php.ini like extension=mongodb.so and extension=excimer.so
sudo nano /etc/php/8.2/cli/php.ini
# 6. phpmyadmin
# 6.2. remove validate password component
sudo mysql
mysql -u root -p
UNINSTALL COMPONENT "file://component_validate_password";
#exit
# 6.3. install phpmyadmin
sudo apt install phpmyadmin
# 6.4. add validate password component again
sudo mysql
mysql -u root -p
INSTALL COMPONENT "file://component_validate_password";
#exit
# 6.5. enable phpmyadmin
sudo ln -s /usr/share/phpmyadmin /var/www/project/phpmyadmin
# 8 xdebug fix
sudo nano /etc/php/8.2/fpm/php.ini
# 8.1 add this line -> xdebug.max_nesting_level = 512 -> if error increase number of frames
# restart php-fpm
sudo systemctl restart php8.2-fpm
# ******************************************************************** project setup ****************************************************************
# 7. project
# 7.1. make directory and clone project
sudo mkdir /var/www/project
sudo git clone https://github.com/username/project.git /var/www/project
# 7.2. add permissions and change owner
sudo sh /var/www/project/fix-permissions.sh
# 7.3. config nginx
sudo cp /var/www/project /etc/nginx/sites-available/project

sudo unlink /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
# 8. setup mysql -> create a database and user
sudo mysql
CREATE DATABASE project;
CREATE USER 'admin'@'%' IDENTIFIED WITH mysql_native_password BY 'password';
GRANT ALL ON project.* TO 'admin'@'%';
#exit
# 9. npm
sudo apt install npm
cd /var/www/project || exit
npm install
# 9.1. for build required css and js files
npm run build
# 10. make .env file and configure it like setup database settings and cache driver and more
sudo cp .env.example .env
php artisan key:generate
# 11.1. server installation
./composer.phar install --optimize-autoloader --no-dev
# 11.2. local installation
./composer.phar install
# 12. copy env files for test environments
cp .env .env.testing
cp .env .env.dusk
# 13. run migrations
php artisan migrate --seed
# 14. clear configs
php artisan optimize:clear
# 15. make filament user -> remember username and password
php artisan make:filament-user
# 16. sync permissions
php artisan permissions:sync
# 17. run with default serve
php artisan serve
# 17.1 in local : add dns to /etc/hosts like 127.0.0.1 server_name -> 127.0.0.1 project.local
sudo nano /etc/hosts
# 18. run queue system
php artisan queue:work
