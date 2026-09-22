# Live deployment

Use a real Linux host or cPanel. Do not put XAMPP or `php artisan serve` on the public internet.

## 1. Server needs

- PHP **8.2+** with: `bcmath`, `ctype`, `curl`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`
- MySQL 8
- Composer
- Node 18+ (or build CSS/JS on your PC and upload `public/build`)
- HTTPS domain pointing at this server

The web server document root must be **`public/`**, not the project folder.

## 2. Upload the project

From Git:

```bash
git clone <your-repo-url> /var/www/loan_app
cd /var/www/loan_app
```

Or upload the project by FTP/SFTP. Do **not** upload `.env`. You can skip `node_modules` and `vendor` if you will install them on the server.

## 3. Live `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Set at least:

```env
APP_NAME=MaxWallet
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Karachi

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maxwallet
DB_USERNAME=your_db_user
DB_PASSWORD=strong_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAXWALLET_OTP_MINUTES=5
MAXWALLET_OTP_MAX_ATTEMPTS=5
```

`APP_URL` must be the exact live URL (https, no trailing slash). A wrong URL breaks login redirects, the favicon, and featured images.

Create the MySQL database and user before running migrations.

```sql
CREATE DATABASE maxwallet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 4. Install and build

On the server:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

First live install only, if you want the demo admin and customers:

```bash
php artisan db:seed --force
```

Then **change the admin password** immediately. The seeded admin is `admin@maxwallet.test` / `password`.

Do **not** run `migrate:fresh` on a live site that already has data.

## 5. Folder permissions (Linux)

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

On some hosts the web user is `apache` or `nginx` instead of `www-data`.

Payment screenshots stay in `storage/app/private`. Featured images use `storage/app/public` through `/storage`.

## 6. Web server

### Apache / cPanel

Point the domain document root to `loan_app/public`. The project already includes `public/.htaccess`.

### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/loan_app/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Add SSL next (cPanel AutoSSL, Certbot, or Cloudflare). After HTTPS is active, keep `APP_URL` on `https://yourdomain.com`.

## 7. After every update

```bash
cd /var/www/loan_app
git pull
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

## 8. Check before you go live

- `https://yourdomain.com/` opens customer login
- `https://yourdomain.com/admin` opens admin login
- Create a customer with an app name, copy only the base app URL, and complete the 4-digit OTP. The app name appears after login.
- Upload a payment screenshot
- Logout, then log in another customer on the same phone

OTP is still a **demo** (no SMS). The code auto-fills after a few seconds. For tests you can enter **1234**. Turn off demo autofill before a real public launch if you add SMS later.

If the page is blank, set `APP_DEBUG=true` only long enough to read `storage/logs/laravel.log`, then set it back to `false`.

## Shared hosting notes

- PHP version must be 8.2 or newer.
- Document root must be `public/`.
- SSH is strongly preferred so you can run Composer and Artisan.
- If the host has no Node, run `npm install` and `npm run build` on your computer, then upload `public/build` and `public/hot` should **not** exist on live.
- If the host cannot create a symlink, ask support to link `public/storage` → `storage/app/public`, or copy featured images another way.
- Never leave `APP_DEBUG=true` on a public domain.
