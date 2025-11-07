#!/usr/bin/env bash
set -euo pipefail

# Usage: sudo bash provision.sh <GIT_REPO_URL> <DOMAIN>
# Example: sudo bash provision.sh https://github.com/you/repo.git your-domain.com

REPO_URL="$1"
DOMAIN="$2"
APP_DIR="/var/www/nexusdigital"
PHP_VERSION=8.2

echo "Provisioning Ubuntu server for NexusDigital..."

# Update and install basic packages
apt update
apt upgrade -y
apt install -y software-properties-common curl git unzip nginx certbot python3-certbot-nginx build-essential

# Add Ondřej Surý PPA for newer PHP versions
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP and extensions
apt install -y php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-cli php${PHP_VERSION}-mbstring php${PHP_VERSION}-xml php${PHP_VERSION}-curl php${PHP_VERSION}-zip php${PHP_VERSION}-bcmath php${PHP_VERSION}-mysql

# Start PHP-FPM
systemctl enable --now php${PHP_VERSION}-fpm

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Create app directory and clone
mkdir -p ${APP_DIR}
chown $SUDO_USER:$SUDO_USER ${APP_DIR}
cd ${APP_DIR}

if [ -d .git ]; then
  echo "Repository already cloned. Pulling latest..."
  sudo -u $SUDO_USER git pull
else
  sudo -u $SUDO_USER git clone ${REPO_URL} .
fi

# Install PHP dependencies
sudo -u $SUDO_USER composer install --no-interaction --no-dev --prefer-dist

# Copy example env if missing
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    sudo -u $SUDO_USER cp .env.example .env
    echo "Copied .env.example to .env — edit .env before continuing"
  else
    echo ".env.example not found. Please create .env in project root."
  fi
fi

# Generate key, migrate, seed, storage link (may fail if DB not configured)
sudo -u $SUDO_USER php artisan key:generate || true
sudo -u $SUDO_USER php artisan migrate --force || true
sudo -u $SUDO_USER php artisan db:seed --force || true
sudo -u $SUDO_USER php artisan storage:link || true

# Set ownership and permissions
chown -R www-data:www-data ${APP_DIR}
find ${APP_DIR} -type f -exec chmod 644 {} \;
find ${APP_DIR} -type d -exec chmod 755 {} \;
chmod -R ug+rw ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache || true

# Nginx site
NGINX_CONF_PATH="/etc/nginx/sites-available/nexusdigital"
cat > ${NGINX_CONF_PATH} <<EOF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${APP_DIR}/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

ln -sf ${NGINX_CONF_PATH} /etc/nginx/sites-enabled/nexusdigital
nginx -t && systemctl reload nginx

# Obtain SSL via Certbot
if command -v certbot >/dev/null 2>&1; then
  certbot --nginx -n --agree-tos --redirect -m admin@${DOMAIN} -d ${DOMAIN} -d www.${DOMAIN} || true
fi

echo "Provisioning finished. Edit ${APP_DIR}/.env and restart services if needed."
