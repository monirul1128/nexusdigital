Oracle Cloud Always Free — quick deploy guide for NexusDigital

Prerequisites
- An Oracle Cloud "Always Free" VM running Ubuntu 22.04 (or any Ubuntu 22.04+ instance)
- A domain name pointed (A record) at the VM's public IP
- SSH access to the VM (you should be able to sudo)
- Your project pushed to a Git remote (e.g. GitHub)

Steps (copy/paste on the VM)
1) Copy the repository and domain into the provision script arguments and run as root (or with sudo):

```bash
sudo bash provision.sh https://github.com/YOUR_USER/YOUR_REPO.git your-domain.com
```

2) Edit the `.env` file in the project root to set DB credentials and any API secrets:

```bash
cd /var/www/nexusdigital
sudo nano .env
# update APP_URL, DB_*, STRIPE_SECRET_KEY, etc.
```

3) Run final artisan commands as the app user:

```bash
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan db:seed --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
```

4) Check Nginx and SSL

```bash
sudo nginx -t
sudo systemctl status nginx
sudo certbot certificates
```

Notes
- The `provision.sh` script installs PHP 8.2 via the Ondřej Surý PPA. Change PHP_VERSION in the script if you want a different version.
- For production, consider creating a dedicated database user and using MySQL/MariaDB (install with `sudo apt install mariadb-server`), or use a managed DB.
- Backups: schedule `tar` or rsync backups for `storage/` and the DB.

Need me to also create a `systemd` unit for queue workers or a Supervisor config? Tell me and I will add it.
