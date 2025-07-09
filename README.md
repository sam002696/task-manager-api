# Laravel Deployment to AWS EC2 (Ubuntu + PHP 8.3)

This guide will help you deploy your Laravel app on an **AWS EC2 Ubuntu instance** running **PHP 8.3**.

---

## Prerequisites

-   AWS EC2 instance with Ubuntu
-   `.pem` SSH key (essential)
-   Laravel project repo: [`task-manager-api`](https://github.com/sam002696/task-manager-api.git)

---

## 1. 🔐 Connect to EC2

```bash
chmod 400 /path/to/your-key.pem
ssh -i /path/to/your-key.pem ubuntu@your-ec2-ip
```

## 2. Clone the Laravel Project

```bash
git clone https://github.com/sam002696/task-manager-api.git
```

## Create web root directory if it doesn't exist:

```bash
sudo mkdir -p /var/www
sudo mv ~/task-manager-api /var/www/task-manager-api
```

## 3. 🔧 Set Permissions

```bash
sudo chown -R $USER:www-data /var/www/task-manager-api/storage
sudo chown -R $USER:www-data /var/www/task-manager-api/bootstrap/cache

sudo chmod -R 775 /var/www/task-manager-api/storage
sudo chmod -R 775 /var/www/task-manager-api/bootstrap/cache
```

## 4. 🛠️ Install PHP & Extensions

```bash
sudo apt update && sudo apt upgrade -y

sudo apt install php php-cli php-mbstring php-xml php-bcmath php-curl php-zip php-mysql php-common php-tokenizer php-gd php-fpm unzip curl -y
```

## 5. 📦 Install Composer

```bash
curl -sS https://getcomposer.org/installer | php

sudo mv composer.phar /usr/local/bin/composer
```

## 6. 📦 Install & Configure MySQL

```bash
sudo apt install mysql-server -y

sudo mysql_secure_installation
```

### To allow external DB access (optional):

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

### Change:

```bash
bind-address = 0.0.0.0
```

### Restart MySQL:

```bash
sudo systemctl restart mysql
```

### Create Database & User

```bash
sudo mysql -u root
```

### Inside MySQL shell:

```bash
CREATE DATABASE laravel_app;
CREATE USER 'laravel_user'@'localhost' IDENTIFIED BY 'YourStrongPasswordHere';
GRANT ALL PRIVILEGES ON laravel_app.* TO 'laravel_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Fix Access Denied Errors (Optional):

```bash
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'YourRootPasswordHere';
FLUSH PRIVILEGES;
```

## 7. ⚙️ Set Up Laravel Environment

```bash
cd /var/www/task-manager-api
sudo cp .env.example .env
sudo nano .env
```

### Update DB section:

```bash
DB_DATABASE=laravel_app
DB_USERNAME=laravel_user
DB_PASSWORD=YourStrongPasswordHere
```

## 8. 📦 Install Laravel Dependencies :

```bash
cd /var/www/task-manager-api
sudo composer install

```

## 9. 🔐 Laravel Key & Migrate :

```bash
sudo php artisan key:generate
sudo php artisan migrate
sudo php artisan config:cache
sudo php artisan route:cache
```

## 10. 🌐 Install & Configure Nginx :

```bash
sudo apt install nginx -y
```

### Stop Apache if it's running:

```bash
sudo systemctl stop apache2
sudo systemctl disable apache2
```

### 📝 Create Nginx Config :

```bash
sudo nano /etc/nginx/sites-available/task-manager-api
```

### Paste the following:

```nginx
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    server_name _;

    root /var/www/task-manager-api/public;

    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 🔗 Enable Site & Disable Default:

```bash
sudo ln -s /etc/nginx/sites-available/task-manager-api /etc/nginx/sites-enabled/task-manager-api
sudo rm /etc/nginx/sites-enabled/default 2>/dev/null || true
```

### 🧪 Test and Restart Nginx:

```bash
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl status nginx
```

### Deployment Complete:

```bash
http://your-ec2-ip/
```

## 🚀 Next Steps: Map Vercel Subdomain + Enable HTTPS with Certbot

This section helps you map your EC2 Laravel API to a subdomain like `api.example.xyz` and secure it with HTTPS.

---

## Step 1: Add A Record in Vercel DNS

1. Go to: [https://vercel.com/dashboard](https://vercel.com/dashboard)
2. Click **Domains** > Choose `example.xyz`
3. Go to the **DNS** tab
4. Click **Add Record**:

```bash
Type: A
Name: api
Value: 13.215.250.73 ← (your EC2 IP)
TTL: 3600
```

-🕓 Wait **5–10 minutes** for DNS propagation.

-   This will map: api.`example.xyz` → your EC2 server

## Step 2: Create Nginx Config for the Subdomain

Instead of using `default_server`, create a dedicated config:

```bash
sudo nano /etc/nginx/sites-available/api.example.xyz
```

```nginx
server {
    listen 80;
    server_name api.example.xyz;

    root /var/www/task-manager-api/public;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Step 3: Enable the Nginx Config

```bash
sudo ln -s /etc/nginx/sites-available/api.example.xyz /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Visit :

```bash
http://api.example.xyz
```

## Step 4: Install Certbot & Enable HTTPS

### Install certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### Request SSL Certificate:

```bash
sudo certbot --nginx -d api.example.xyz
```

-   Certbot will auto-detect the config.
-   Say YES to "Redirect HTTP to HTTPS".

## Step 5: Verify HTTPS

```bash
https://api.example.xyz
```

-   You should see your Laravel API with a secure 🔒 HTTPS connection.
