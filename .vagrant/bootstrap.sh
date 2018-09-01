#!/usr/bin/env bash
# Borrowed (with modifications) from: https://github.com/spiritix/vagrant-php7/blob/master/bootstrap.sh
# And https://gist.github.com/DragonBe/3736616a329c57460ccd0dace99a61fa

# Fix for https://bugs.launchpad.net/ubuntu/+source/livecd-rootfs/+bug/1561250
if ! grep -q "ubuntu-xenial" /etc/hosts; then
    echo "127.0.0.1 ubuntu-xenial" >> /etc/hosts
fi

Update () {
    echo "-- Update packages --"
    sudo apt-get update
    sudo apt-get upgrade
}
Update

echo "-- Prepare configuration for MySQL --"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password root"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password root"

echo "-- Install PPA's --"
sudo add-apt-repository ppa:ondrej/php -y
Update

echo "-- Install packages [ Tools and Helpers ]--"
sudo apt-get install -y --force-yes python-software-properties curl git git-core htop mc

echo "-- Install packages [ PHP 7.2 ] --"
sudo apt-get install -y --force-yes php7.2 php7.2-bcmath php7.2-bz2 php7.2-cli php7.2-curl php7.2-intl php7.2-json php7.2-mbstring php7.2-opcache php7.2-soap php7.2-sqlite3 php7.2-xml php7.2-xsl php7.2-zip php7.2-xdebug

echo "-- Install packages [ Apache ] --"
sudo apt-get install -y --force-yes apache2 libapache2-mod-php7.2

echo "-- Install packages [ MySQL ] --"
sudo apt-get install -y --force-yes mysql-server

echo "-- Install packages [ Node ] --"
sudo curl -sL https://deb.nodesource.com/setup_8.x | sudo -E bash -
sudo apt-get install -y --force-yes nodejs build-essential

echo "-- Configure [ Https Certificate ] --"
sudo openssl req -x509 -newkey rsa:2048 -keyout mykey.key -out mycert.pem -days 365 -nodes -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=example.com"
sudo cp mycert.pem /etc/ssl/certs
sudo cp mykey.key /etc/ssl/private

echo "-- Configure [ PHP & Apache ] --"
sudo sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php/7.0/apache2/php.ini
sudo sed -i "s/display_errors = .*/display_errors = On/" /etc/php/7.0/apache2/php.ini
sudo sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php/7.0/apache2/php.ini
sudo cp /vagrant_data/.vagrant/000-default.conf /etc/apache2/sites-available/000-default.conf
sudo a2enmod actions php7.2 alias rewrite ssl
sudo service apache2 restart

echo "-- Install Composer --"
if [ -e /usr/local/bin/composer ]; then
    /usr/local/bin/composer self-update
else
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "-- Setup databases --"
sudo sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/my.cnf
sudo mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON *.* TO 'homestead'@'%' IDENTIFIED BY 'secret' WITH GRANT OPTION; FLUSH PRIVILEGES;"
mysql -uroot -proot -e "CREATE DATABASE homestead";
sudo service mysql restart

echo "-- Run App Install --"
npm install -g requirejs uglify-js jade bower
composer --working-dir=/vagrant_data install

cd /vagrant_data/resources/js/
bower --allow-root install almond requirejs requirejs-text jade