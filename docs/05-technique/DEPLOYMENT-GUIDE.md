# 🚀 Guide de Déploiement - JLC Workforce Suite

## 🎯 Vue d'ensemble

Guide complet pour déployer le SaaS JLC Workforce Suite en production avec architecture multi-tenant.

---

## 🏗️ Architecture de Production

### **Stack Technique**
- **Backend** : Laravel 11 + PostgreSQL + Redis
- **Frontend** : Laravel Blade + Alpine.js + Tailwind CSS
- **Mobile** : Flutter 3.x + API REST
- **Infrastructure** : VPS Ubuntu 22.04 + Nginx

### **Services Requis**
- **PostgreSQL 14+** - Base de données principale
- **Redis 6+** - Cache et sessions
- **Nginx** - Serveur web et reverse proxy
- **SSL** - Certificats Let's Encrypt
- **Backup** - Sauvegardes automatiques

---

## 🖥️ Configuration Serveur

### **Spécifications Minimales**
- **CPU** : 2 vCPU
- **RAM** : 4 GB
- **Stockage** : 50 GB SSD
- **Bande passante** : 1 TB/mois

### **Spécifications Recommandées**
- **CPU** : 4 vCPU
- **RAM** : 8 GB
- **Stockage** : 100 GB SSD
- **Bande passante** : 2 TB/mois

---

## 📦 Installation Serveur

### **1. Mise à jour du système**
```bash
sudo apt update && sudo apt upgrade -y
```

### **2. Installation des dépendances**
```bash
# PHP 8.2
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-pgsql php8.2-redis php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-gd php8.2-bcmath

# PostgreSQL
sudo apt install postgresql postgresql-contrib

# Redis
sudo apt install redis-server

# Nginx
sudo apt install nginx

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs
```

### **3. Configuration PostgreSQL**
```bash
sudo -u postgres psql
```

```sql
CREATE DATABASE jlc_workforce_suite;
CREATE USER jlc_user WITH PASSWORD 'secure_password';
GRANT ALL PRIVILEGES ON DATABASE jlc_workforce_suite TO jlc_user;
\q
```

### **4. Configuration Redis**
```bash
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

---

## 🚀 Déploiement Application

### **1. Cloner le projet**
```bash
cd /var/www
sudo git clone https://github.com/your-repo/jlc-workforce-suite.git
sudo chown -R www-data:www-data jlc-workforce-suite
cd jlc-workforce-suite
```

### **2. Configuration environnement**
```bash
cp .env.example .env
nano .env
```

**Configuration production** :
```env
APP_NAME="JLC Workforce Suite"
APP_ENV=production
APP_KEY=base64:your_generated_key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=jlc_workforce_suite
DB_USERNAME=jlc_user
DB_PASSWORD=secure_password

CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### **3. Installation des dépendances**
```bash
composer install --optimize-autoloader --no-dev
npm install && npm run build
```

### **4. Configuration Laravel**
```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **5. Permissions**
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 🌐 Configuration Nginx

### **Fichier de configuration**
```bash
sudo nano /etc/nginx/sites-available/jlc-workforce-suite
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/jlc-workforce-suite/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### **Activer le site**
```bash
sudo ln -s /etc/nginx/sites-available/jlc-workforce-suite /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 Configuration SSL

### **Installation Certbot**
```bash
sudo apt install certbot python3-certbot-nginx
```

### **Obtenir le certificat SSL**
```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### **Renouvellement automatique**
```bash
sudo crontab -e
```

Ajouter :
```cron
0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 📊 Monitoring et Logs

### **Configuration des logs**
```bash
sudo nano /etc/rsyslog.d/50-jlc-workforce-suite.conf
```

```conf
# Logs Laravel
:programname, isequal, "jlc-workforce-suite" /var/log/jlc-workforce-suite/laravel.log
& stop
```

### **Monitoring avec Laravel Telescope** (optionnel)
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

---

## 💾 Sauvegardes

### **Script de sauvegarde**
```bash
sudo nano /usr/local/bin/backup-jlc.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/jlc-workforce-suite"
DB_NAME="jlc_workforce_suite"

# Créer le dossier de sauvegarde
mkdir -p $BACKUP_DIR

# Sauvegarde base de données
pg_dump $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Sauvegarde fichiers
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/jlc-workforce-suite

# Supprimer les sauvegardes de plus de 30 jours
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Sauvegarde terminée : $DATE"
```

### **Crontab pour sauvegardes**
```bash
sudo crontab -e
```

```cron
# Sauvegarde quotidienne à 2h du matin
0 2 * * * /usr/local/bin/backup-jlc.sh
```

---

## 🔄 Déploiement Continu

### **Script de déploiement**
```bash
sudo nano /usr/local/bin/deploy-jlc.sh
```

```bash
#!/bin/bash
cd /var/www/jlc-workforce-suite

# Sauvegarde avant déploiement
/usr/local/bin/backup-jlc.sh

# Récupérer les dernières modifications
git pull origin main

# Installer les dépendances
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Migrations
php artisan migrate --force

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Redémarrer les services
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx

echo "Déploiement terminé"
```

---

## 🚨 Sécurité

### **Configuration Firewall**
```bash
sudo ufw enable
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw allow 5432  # PostgreSQL (si accès externe)
```

### **Sécurisation PostgreSQL**
```bash
sudo nano /etc/postgresql/14/main/postgresql.conf
```

```conf
listen_addresses = 'localhost'
```

### **Sécurisation Redis**
```bash
sudo nano /etc/redis/redis.conf
```

```conf
bind 127.0.0.1
requirepass your_redis_password
```

---

## 📈 Performance

### **Optimisation PHP-FPM**
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

```conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 1000
```

### **Optimisation Nginx**
```bash
sudo nano /etc/nginx/nginx.conf
```

```conf
worker_processes auto;
worker_connections 1024;

gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
```

---

## 🧪 Tests de Déploiement

### **Vérifications post-déploiement**
```bash
# Test de santé
curl https://your-domain.com/api/health

# Test de l'API
curl https://your-domain.com/api/v1/jobs

# Vérification des logs
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

### **Tests de charge** (optionnel)
```bash
# Installation Apache Bench
sudo apt install apache2-utils

# Test de charge
ab -n 1000 -c 10 https://your-domain.com/api/v1/jobs
```

---

## 🔧 Maintenance

### **Mise à jour des dépendances**
```bash
cd /var/www/jlc-workforce-suite
composer update
npm update
npm run build
php artisan config:cache
```

### **Nettoyage des logs**
```bash
sudo find /var/log -name "*.log" -mtime +30 -delete
```

### **Monitoring de l'espace disque**
```bash
df -h
du -sh /var/www/jlc-workforce-suite
```

---

## 📞 Support

### **Logs importants**
- **Nginx** : `/var/log/nginx/`
- **PHP-FPM** : `/var/log/php8.2-fpm.log`
- **Laravel** : `/var/www/jlc-workforce-suite/storage/logs/`
- **PostgreSQL** : `/var/log/postgresql/`

### **Commandes utiles**
```bash
# Redémarrer les services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart postgresql
sudo systemctl restart redis-server

# Vérifier le statut
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status postgresql
sudo systemctl status redis-server
```

---

*Guide de déploiement v1.0*
*Mise à jour : 30/01/2025*